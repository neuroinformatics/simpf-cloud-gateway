<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimPF\Utility\SshConnection;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Test description';

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
        /*
        $cmd = 'moveResultFile';
        $sid = 'df631991a6ee9766c9ca9950d5092b5c';
        $ret = SshConnection::runAsLocalUser($cmd, $sid);
        var_dump($ret);
        */
        /*
        $host = '192.168.2.3';
        $cmd = 'ls';
        $ret = SshConnection::runOnVirtualMachine($host, $cmd);
        var_dump($ret);
        */
    }
}
