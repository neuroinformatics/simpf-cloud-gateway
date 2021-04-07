<?php

namespace SimPF\Utility;

use SimPF\Config;

class SshConnection
{
    /**
     * run as local user.
     *
     * @param string   $cmd
     * @param string[] $args
     *
     * @return bool
     */
    public static function runAsLocalUser($cmd, ...$args)
    {
        $config = Config::get('ssh');
        $param = $config['localhost'];
        $cmds = [escapeshellarg(Config::getCommandPath($cmd))];
        foreach ($args as $arg) {
            $cmds[] = escapeshellarg($arg);
        }
        $ssh = self::getConnection($param['hostname'], $param['username'], Config::getFileContents($param['private_key']), $param['passphrase']);
        if (null === $ssh) {
            return false;
        }
        $ret = $ssh->exec(implode(' ', $cmds));
        if (0 != $ssh->getExitStatus()) {
            return false;
        }

        return true;
    }

    /**
     * run on virtual machine.
     *
     * @param string   $hostname
     * @param string   $cmd
     * @param string[] $args
     *
     * @return bool
     */
    public static function runOnVirtualMachine($hostname, $cmd, ...$args)
    {
        $config = Config::get('ssh');
        $param = $config['virtualmachine'];
        $cmds = [escapeshellarg($cmd)];
        foreach ($args as $arg) {
            $cmds[] = escapeshellarg($arg);
        }
        $ssh = self::getConnection($hostname, $param['username'], Config::getFileContents($param['private_key']), $param['passphrase']);
        if (null === $ssh) {
            return false;
        }
        $ret = $ssh->exec(implode(' ', $cmds));
        if (0 != $ssh->getExitStatus()) {
            return false;
        }

        return true;
    }

    /**
     * get connction.
     *
     * @param string $hostname
     * @param string $usernmae
     * @param string $privatekey
     * @param string $passphrase
     *
     * return \phpseclib3\Net\SSH2|null
     */
    private static function getConnection($hostname, $username, $privatekey, $passphrase)
    {
        try {
            $ssh = new \phpseclib3\Net\SSH2($hostname);
            $key = \phpseclib3\Crypt\PublicKeyLoader::load($privatekey, $passphrase);
            if (!$ssh->login($username, $key)) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

        return $ssh;
    }
}
