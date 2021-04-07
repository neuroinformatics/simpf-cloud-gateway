<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimPF\Utility\DesktopManager;

class TerminateReadyVmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:terminateReadyVms {hostname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate ready virtual machies';

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
        $hostname = $this->argument('hostname');
        $res = DesktopManager::terminateReadyVms($hostname);
        if (false === $res['status']) {
            $this->error($res['message']);

            return 1;
        }

        return 0;
    }
}
