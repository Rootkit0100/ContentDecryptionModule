<?php

namespace ContentDecryptionPlugin\Jobs;

use ContentDecryptionPlugin\Models\StreamMpd;
use ContentDecryptionPlugin\Services\StreamMpdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RestartMpdRtmpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;

    public $tries = 1;

    public function handle()
    {
        $mpdStreams = StreamMpd::where('status', '=', 'started')->get();

        foreach ($mpdStreams as $mpd) {
            $streamMpdService = new StreamMpdService($mpd);
            $isRunning = $streamMpdService->isRtmpProcessRunning();
            $isRunningNm3u8 = $streamMpdService->isNm3uRunning();

            if (!$isRunning) {
                $streamMpdService->startRtmpProcess();
            }

            if (!$isRunningNm3u8) {
                $streamMpdService->startMpdStream();
            }
        }
    }

}

