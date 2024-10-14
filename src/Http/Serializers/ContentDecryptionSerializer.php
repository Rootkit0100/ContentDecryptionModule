<?php

namespace ContentDecryptionPlugin\Http\Serializers;
use App\Http\Serializers\Serializer;
use App\Models\StreamingServer;

class ContentDecryptionSerializer extends Serializer
{
    public function transform($data)
    {
        $details = [
            'id' => $data->id,
            'name' => $data->name,
            'status' => $data->status,
            'process_pid' => $data->pid,
            'server_name' => $this->getServerName($data->server_id),
            'url' => $data->url,
            'decryption_key' => $data->decryption_key,
            'rtmp_url' => $data->rtmp_url,
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
        ];

        return $details;
    }

    protected function getServerName($serverId)
    {
        $server = StreamingServer::where('id', '=', $serverId)
            ->first();

        return $server->name;
    }
}

