<?php

namespace ContentDecryptionPlugin\Services;
use App\Utils\System;
use App\Models\StreamingServer;

class StreamMpdService
{
    private $server;
    private $connection;
    /**
     * @var mixed
     */
    protected $streamMpd;

    protected $basePath = '/home/onestream/iptv/packages/1s-extra/contentdecryption/';

    public function __construct($streamMpd)
    {
        $this->streamMpd = $streamMpd;

        $this->server = $this->findServer();

        $this->connection = $this->initSshConnection();
    }
    public function startMpdStream()
    {
        if (!$this->connection) {
            \Log::debug('Cant Connect to server with ID:' . $this->server->id);
            return ['error' => 'Cant Connect to Server'];
        }

        $ffmpegPath = $this->basePath .'/bin/play2';

        if (!$this->doesFileExist($ffmpegPath)) {
            $this->connection->exec('ln -s /usr/bin/ffmpeg ' . $ffmpegPath);
        }

        $command = $this->prepareDownloadAndDecrCommand($ffmpegPath);

        \Log::debug('Generate Download and Decrypting Command ' . $command);

        $this->connection->exec('mkdir -p ' . $this->basePath.'storage/');

        $pidFile = $this->basePath.'storage/'.$this->streamMpd->id. '.pid';

        $this->connection->exec('echo ' . escapeshellarg($command . "> /dev/null 2>&1 & echo $! > $pidFile") . ' > /script.sh' );

        $this->connection->exec('mv /script.sh '.  $this->basePath. 'storage/ && cd ' . $this->basePath .'storage && nohup bash ./script.sh > /dev/null 2>&1 & ');

        $pid = $this->readPidFile($pidFile);

        if (!$pid) {
            \Log::debug('Cant Get PID from file:' . $pidFile);
            return ['error' => 'Cant Get PID from file'];
        }

        $this->updatePid($pid);

        $this->updateStatus('started');

        $this->startPushingMpdStream();
    }

    public function isRtmpProcessRunning()
    {
        \Log::debug('Check if ffmpeg with pid :' .$this->streamMpd->ffmpeg_pid. ' is running');
        $result = $this->connection->exec('ps -ef | grep '.$this->streamMpd->ffmpeg_pid.' | grep -v grep');

        \Log::debug(var_export($result, true));
        if (empty($result)) {
            \Log::debug('Process ' . $this->streamMpd->ffmpeg_pid .' not running');
            return false;
        }
        \Log::debug('Process ' . $this->streamMpd->ffmpeg_pid .' is running');
        return true;
    }


    public function isNm3uRunning()
    {
        \Log::debug('Check if Nm3u8 with pid :' .$this->streamMpd->ffmpeg_pid. ' is running');
        $result = $this->connection->exec('ps -ef | grep '.$this->streamMpd->pid.' | grep -v grep');

        \Log::debug(var_export($result, true));
        if (empty($result)) {
            \Log::debug('Process ' . $this->streamMpd->pid .' not running');
            return false;
        }
        \Log::debug('Process ' . $this->streamMpd->pid .' is running');
        return true;
    }

    protected function startPushingMpdStream()
    {
        $playlist = $this->basePath. 'storage/live_playlist_'. $this->streamMpd->id.'.m3u8';

        $pidFile = $this->basePath.'storage/'.'ffmpeg_'.$this->streamMpd->id. '.pid';

        $ffmpegPath = $this->basePath .'/bin/play2';

        $logPath = $this->basePath.'/storage/'.'log_ffmpeg_'.$this->streamMpd->id. '.log';
        //before start remove the old files for stream

        $this->waitForPlaylist($playlist);

        $command = $this->prepareRtmpPushCommand($playlist, $ffmpegPath);

        \Log::debug('Generate Command For Push: ' . $command);

        $this->connection->exec($command . " > /dev/null 2> $logPath & echo $! > $pidFile");

        $pid = $this->readPidFile($pidFile);

        $this->updateFfmpegPid($pid);
    }

    public function stopMpdStream()
    {
        \Log::debug('Stoping Mpd stream with ID: '. $this->streamMpd->pid);
        $this->connection->exec('kill -9 ' . $this->streamMpd->pid);

        $this->connection->exec('kill -9 ' . $this->streamMpd->ffmpeg_pid);

        $this->updatePid(0);

        $this->updateFfmpegPid(0);

        $this->updateStatus('stopped');

        $this->deleteFiles();
    }

    public function startRtmpProcess()
    {
        \Log::debug('Restarting ffmpeg rtmp process stream mpd id: '. $this->streamMpd->id);
        $this->startPushingMpdStream();
    }

    protected function prepareDownloadAndDecrCommand($ffmpegPath)
    {
        $wrapperBinPath = $this->basePath.'bin/N_m3u8DL-RE';

        $decryptBinPath = $this->basePath.'bin/packager-linux-x64';
// 0:03 /usr/bin/ffmpeg -y -nostdin -hide_banner -err_detect ignore_err -loglevel warning -headers User-Agent: OneStream IPTV v2.0.7-3 -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0 -probesize 5000000 -analyzeduration 5000000 -progress /home/onestream/iptv/storage/app//streams/a570e1b7-c2be-49af-9dc7-1ed6e010e99a.txt -i http://012345x.com:999/live/Users-personal/123456789/191785.m3u8 -sn -vcodec copy -acodec copy -f hls -hls_segment_type mpegts -hls_time 10 -hls_allow_cache 1 -hls_start_number_source epoch -hls_ts_options mpegts_flags=+initial_discontinuity:mpegts_copyts=1 -hls_delete_threshold 5 -hls_list_size 5 -hls_flags delete_segments+discont_start+omit_endlist -hls_segment_filename /home/onestream/iptv/storage/app//streams/a570e1b7-c2be-49af-9dc7-1ed6e010e99a_%d.ts /home/onestream/iptv/storage/app//streams/a570e1b7-c2be-49af-9dc7-1ed6e010e99a.m3u8 -vcodec copy -acodec copy -bsf:a aac_adtstoasc -sn -f flv rtmp://94.155.35.12:4499/live/a570e1b7-c2be-49af-9dc7-1ed6e010e99a
        $command = "export RE_LIVE_PIPE_OPTIONS='-c copy -f hls -hls_segment_type mpegts -hls_time 10  -hls_delete_threshold 5 -hls_list_size 11  -hls_flags delete_segments ./live_playlist_".$this->streamMpd->id.".m3u8'; {WRAPPER_BIN} '{URL}' --key {KEY}  --use-shaka-packager true --ffmpeg-binary-path {FFMPEG_BIN} --decryption-binary-path {DECRYPT_BIN} --del-after-done true --log-level debug --live-keep-segments false --download-retry-count 1  --live-pipe-mux true  --live-take-count 5 --check-segments-count false --no-date-info --auto-select  --save-name ".$this->streamMpd->id;

        $command = str_replace(
            ['{WRAPPER_BIN}', '{URL}', '{KEY}', '{FFMPEG_BIN}', '{DECRYPT_BIN}'],
            [
                $wrapperBinPath,
                $this->streamMpd->url,
                $this->streamMpd->decryption_key,
                $ffmpegPath,
                $decryptBinPath,
            ],
            $command
        );

        return $command;
    }

    protected function deleteFiles()
    {
        //todo some of files are not deleted
        $playlist = $this->basePath. 'storage/live_playlist_'. $this->streamMpd->id.'.m3u8';

        $nm3u8Pid = $this->basePath. 'storage/'. $this->streamMpd->id.'.pid';

        $segments = $this->basePath. 'storage/live_playlist_'. $this->streamMpd->id.'*' ;

        $ffmpegPidFile = $this->basePath.'storage/'.'ffmpeg_'.$this->streamMpd->id. '.pid';

        $this->connection->exec( 'rm -rf '.$this->basePath .'storage/'. $this->streamMpd->id);

        $logPath = $this->basePath.'/storage/'.'log_ffmpeg_'.$this->streamMpd->id. '.log';

        $this->connection->exec('rm '. $playlist .' ; rm ' . $ffmpegPidFile);

        $this->connection->exec('rm ' . $logPath . '; rm -r '.$segments  .'; rm '. $nm3u8Pid);

    }

    protected function readPidFile($pidFile)
    {
        $command = 'cat ' . $pidFile;

        \Log::debug('Trying to read the pid from file '. $pidFile);

        $fileContent = $this->connection->exec($command);

        return $fileContent;
    }

    protected function updateFfmpegPid($pid)
    {
        $this->streamMpd->ffmpeg_pid = $pid;

        $this->streamMpd->save();
    }
    protected function updatePid($pid)
    {
        $this->streamMpd->pid = $pid;

        $this->streamMpd->save();
    }

    protected function updateStatus($status)
    {
        $this->streamMpd->status = $status;

        $this->streamMpd->save();
    }

    protected function prepareRtmpPushCommand($playlist, $ffmpegPath)
    {
        $command = $ffmpegPath . ' -y -nostdin -hide_banner -err_detect ignore_err -loglevel debug  -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0 -probesize 50000000 -analyzeduration 50000000  -rw_timeout 10000000 -i {M3U8_INPUT}  -vcodec copy -acodec copy -bsf:a aac_adtstoasc -sn -f flv -rtmp_buffer 4000 -bufsize 8000k -flvflags no_duration_filesize {RTMP_PUSH} ';

        return str_replace(
            ['{M3U8_INPUT}', '{RTMP_PUSH}'],
            [
                $playlist,
                $this->streamMpd->rtmp_url,
            ],
            $command
        );
    }

    private function initSshConnection()
    {
        $connectionFactory = app(\App\Http\Services\ServerConnectionFactory::class);

        $host = $this->server->ip;

        $port = $this->server->ssh_port ?? 22;

        $password = $this->server->ssh_password;

        $user = 'root';

        return $connectionFactory->getSshConnection($host, $port, $user, $password);
    }

    private function findServer()
    {
        $server = StreamingServer::find($this->streamMpd->server_id);

        return $server;
    }

    private function waitForPlaylist(string $playlist)
    {
        $counter = 20;
        \Log::debug('waiting till playlist is generated');
        for ($i = 1; $i <= $counter; $i++) {
            if (!$this->doesFileExist($playlist)) {
                sleep(3);
            }else {
                \Log::debug($playlist . ' is Ready');
                return;
            }
        }
        //maybe throw error

    }

    private function doesFileExist($filePath)
    {
        $command = 'ls ' . escapeshellarg($filePath) . ' > /dev/null 2>&1; echo $?';

        $result = trim($this->connection->exec($command));

        return $result === '0';
    }

}

