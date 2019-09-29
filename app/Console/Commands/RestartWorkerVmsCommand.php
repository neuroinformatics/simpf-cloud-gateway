<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimPF\Utility\DesktopManager;

class RestartWorkerVmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:restartWorkerVms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart worker virtual machines';

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
        $ret = DesktopManager::restartWorkerVms();
        if (false == $ret['status']) {
            $this->error($ret['message']);
        }
    }
}
