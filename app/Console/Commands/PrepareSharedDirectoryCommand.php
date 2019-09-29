<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimPF\Utility\SharedDirectoryManager;

class PrepareSharedDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:prepareSharedDirectory {ip} {url} {dsize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare shared directory';

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

        $ip = $this->argument('ip');
        $url = $this->argument('url');
        $dsize = $this->argument('dsize');

        $res = SharedDirectoryManager::prepare($ip, $url, $dsize);
        if (false === $res['status']) {
            $this->error($res['message']);

            return 1;
        }
    }
}
