<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimPF\Utility\DesktopManager;

class PingWorkerVmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pingWorkerVm {hostname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping to working virtual machine';

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
        $hostname = $this->argument('hostname');

        $res = DesktopManager::pingWorkerVm($hostname);
        if (false === $res['status']) {
            $this->error($res['message']);

            return 1;
        }

        return 0;
    }
}
