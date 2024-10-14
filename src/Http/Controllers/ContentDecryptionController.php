<?php

namespace ContentDecryptionPlugin\Http\Controllers;

use App\Models\StreamingServer;
use ContentDecryptionPlugin\Http\Requests\ContentDecryptionRequest;
use ContentDecryptionPlugin\Http\Serializers\ContentDecryptionSerializer;
use ContentDecryptionPlugin\Models\StreamMpd;
use ContentDecryptionPlugin\Plugin;
use ContentDecryptionPlugin\Services\StreamMpdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ContentDecryptionController extends \App\Http\Controllers\Api\ApiController
{

    public function create()
    {
        $servers = StreamingServer::where('health_status', '=', 'online')
            ->get(['id', 'ip', 'name', 'health_status']);
        $rtmpServers = StreamingServer::where('health_status', '=', 'online')->where('has_rtmp','=', true)
            ->get(['id', 'ip', 'name', 'health_status']) ?? [];

        return view('content_decryption::pages.content_decryption.create')
            ->with('servers', $servers)
            ->with('rtmpServers', $rtmpServers);
    }

    public function store(ContentDecryptionRequest $contentDecryptionRequest)
    {
        $data = $contentDecryptionRequest->validated();
        $data['status'] = 'stopped';
        $data['pid'] = 0;
        $data['rtmp_url'] = $this->buildRtmpUrl($data['rtmp_server_id'], $data['name']);
        \Log::error($data['rtmp_url']);
        $streamMpd = StreamMpd::create($data);

        return Redirect::route('content_decryption.edit', ['stream_mpd' => $streamMpd])->with(['message' => __('message.create')]);
    }

    public function edit(StreamMpd $stream_mpd)
    {
        $servers = StreamingServer::where('health_status', '=', 'online')
            ->get(['id', 'ip', 'name', 'health_status']);
        $rtmpServers = StreamingServer::where('health_status', '=', 'online')->where('has_rtmp','=', true)
            ->get(['id', 'ip', 'name', 'health_status']);
        return view('content_decryption::pages.content_decryption.edit')
            ->with('servers', $servers)
            ->with('streamMpd', $stream_mpd)
            ->with('rtmpServers', $rtmpServers);
    }

    public function update(StreamMpd $stream_mpd, ContentDecryptionRequest  $request)
    {
        $validated = $request->validated();

        $oldServerId = $stream_mpd->server_id;

        $newServerId = $validated['server_id'];

        $this->detectServerChange($oldServerId, $newServerId, $stream_mpd);
        $validated['rtmp_url'] = $this->buildRtmpUrl($validated['rtmp_server_id'], $validated['name']);
        $stream_mpd->update($validated);

        $servers = StreamingServer::where('health_status', '=', 'online')
            ->get(['id', 'ip', 'name', 'health_status']);
        $rtmpServers = StreamingServer::where('health_status', '=', 'online')->where('has_rtmp','=', true)
            ->get(['id', 'ip', 'name', 'health_status']) ?? [];

        return view('content_decryption::pages.content_decryption.edit')->with('servers', $servers)->with('rtmpServers', $rtmpServers)
            ->with('streamMpd', $stream_mpd)
            ->with(['message' => __('message.create')]);

    }

    public function index()
    {
        return view('content_decryption::pages.content_decryption.index');
    }

    public function data(Request $request)
    {
        $streamQuery = StreamMpd::query()
            ->orderBy('created_at', 'desc');

        $allCount = $streamQuery->count();

        if ($search = $request->get('search')) {
            $title = $search['value'] ?? '';
            $streamQuery->where(function ($q) use ($title) {
                $q->where('name', 'ilike', '%' . $title . '%');
            });
        }

        $filteredCount = $streamQuery->count();

        if ($request->has(['start', 'length'])) {
            $start = intval($request->get('start'));
            $length = intval($request->get('length'));

            $streamQuery->skip($start)->take($length);
        }

        $links = $streamQuery->get();

        $serializer = new ContentDecryptionSerializer();
        $this->serializer = $serializer;
        $serialized = $this->serializer->collection($links);

        $response = [
            'draw' => intval($request->get('draw', 0)),
            'recordsTotal' => $allCount,
            'recordsFiltered' => $filteredCount,
            'data' => $serialized,
        ];

        return $this->respond($response);
    }

    public function start(StreamMpd $streamMpd)
    {
        $streamMpdService = new StreamMpdService($streamMpd);
        $streamMpdService->stopMpdStream();
        $output = $streamMpdService->startMpdStream();

        $response = ['status' => 'success', 'message' => __('message.success')];

        if(isset($output['error'])) {
            $response = ['status' => 'error', 'message' => 'message.failed'];
        }
        return response()->json($response);

    }

    public function stop(StreamMpd $streamMpd)
    {
        $streamMpdService = new StreamMpdService($streamMpd);
        $streamMpdService->stopMpdStream();
        return response()->json(['status' => 'success', 'message' => __('message.success')]);
    }

    public function destroy(StreamMpd $streamMpd)
    {
        $streamMpdService = new StreamMpdService($streamMpd);
        $streamMpdService->stopMpdStream();
        sleep(1);
        $streamMpd->delete();

        return response()->json(['status' => 'success', 'message' => __('message.success')]);
    }

    protected function detectServerChange($oldServerId, $newServerId, $streamMpd)
    {
        if ($oldServerId != $newServerId) {
            //we need to stop the process on the old server
            $streamMpdService = new StreamMpdService($streamMpd);
            $streamMpdService->stopMpdStream();
        }
    }

    private function buildRtmpUrl($rtmp_server_id, $name)
    {
        $server = StreamingServer::find($rtmp_server_id);
        $name = str_replace(' ', '_', $name);
        $url = 'rtmp://'. (empty($server->rtmp_domain) ? $server->ip : $server->rtmp_domain) . ':'.$server->rtmp_port.'/live/'.$name;
        return $url;
    }
}
