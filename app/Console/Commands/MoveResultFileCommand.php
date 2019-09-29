<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimPF\Utility\DesktopManager;

class MoveResultFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:moveResultFile {sid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move result file to storage directory';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $uid = posix_getuid();
        if (1000 !== $uid) {
            $this->error('Unexpected Error: this command have to run as ssadmin(uid:1000) user');

            return 1;
        }
        $sid = $this->argument('sid');
        $disk = Storage::disk('local');
        $sfpath = 'downloads/'.$sid.'.zip';
        if (!$disk->exists($sfpath)) {
            $ret = DesktopManager::moveResultFileForDownload($sid, $disk->path($sfpath));
            if (false === $ret['status']) {
                $this->error($ret['message']);

                return 1;
            }
        }
    }
}
