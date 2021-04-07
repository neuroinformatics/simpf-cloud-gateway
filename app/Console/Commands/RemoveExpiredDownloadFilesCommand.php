<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimPF\Utility\DesktopManager;

class RemoveExpiredDownloadFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:removeExpiredDownloadFiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired download files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $uid = posix_getuid();
        if (1000 !== $uid) {
            $this->error('Unexpected Error: this command have to run as ssadmin(uid:1000) user');

            return 1;
        }
        $disk = Storage::disk('local');
        $dpath = $disk->path('downloads');
        $res = DesktopManager::removeExpiredDownloadFiles($dpath);
        if (false === $res['status']) {
            $this->error($res['message']);

            return 1;
        }

        return 0;
    }
}
