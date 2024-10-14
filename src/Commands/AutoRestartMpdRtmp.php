<?php

namespace ContentDecryptionPlugin\Commands;

use App\Console\Commands\LoggingCommand;

class AutoRestartMpdRtmp extends LoggingCommand
{
    protected $signature = 'mpd-rtmp:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Restart mpd streams';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //check and restart rtmp command
    }
}
