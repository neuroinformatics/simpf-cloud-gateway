<?php

namespace SimPF\Utility;

use SimPF\Config;
use SimPF\Mutex;
use SimPF\Guacamole\SessionManager;

class DesktopManager
{
    /**
     * dispatch.
     *
     * @param string $type
     * @param string $url
     * @param string $dsize
     *
     * @return ['sid' => string|false, 'error' => string]
     */
    public static function dispatch($type, $url, $dsize)
    {
        $remoteDesktop = Config::get('remote_desktop');
        $response = function ($sid, $error) {
            return ['sid' => $sid, 'error' => $error];
        };

        if (!isset($remoteDesktop[$type])) {
            return $response(false, 'Unexpected Error: No configurated type detected.');
        }
        $protocol = $remoteDesktop[$type]['protocol'];
        $cmd = $remoteDesktop[$type]['command'];
        $password = $remoteDesktop[$type]['password'];

        // find free virtual machine
        $ip = VirtualMachineManager::getReadyVmIp($type);
        if (false === $ip) {
            return $response(false, 'No free virtual machine available, please try again later.');
        }

        // prepare working directory
        if (false === SshConnection::runAsLocalUser('prepareSharedDirectory', $ip, $url, $dsize)) {
            VirtualMachineManager::restoreVmState($ip);

            return $response(false, 'Failed to download contents.');
        }
        if ('vnc' == $protocol) {
            // start vnc server and run autorun script.
            if (false === SshConnection::runOnVirtualMachine($ip, $cmd, $password)) {
                VirtualMachineManager::restoreVmState($ip);

                return $response(false, 'Failed to run vnc server.');
            }
        }

        // prepare desktop connection session
        $sid = self::prepareSession($ip, $type, $dsize);
        if (false === $sid) {
            return $response(false, 'failed to prepare remote desktop connection');
        }

        return $response($sid, '');
    }

    /**
     * ping working vm.
     *
     * @param string $hostname
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function pingWorkerVm($hostname)
    {
        $response = function ($status, $message) {
            return ['status' => $status, 'message' => $message];
        };

        $sid = SessionManager::getSessionId($hostname);
        if (false === $sid) {
            return $response(false, 'Remote desktop session not found');
        }
        if (false === VirtualMachineManager::isWorking($hostname)) {
            return $response(false, 'Working VM not found');
        }

        return $response(true, 'Success');
    }

    /**
     * move result file for download.
     *
     * @param string $sid
     * @param string $dfpath
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function moveResultFileForDownload($sid, $dfpath)
    {
        $lock = 'session';
        $response = function ($status, $message, $lock) {
            if ('' !== $lock) {
                Mutex::release($lock);
            }

            return ['status' => $status, 'message' => $message];
        };

        if (Mutex::lock($lock, 10)) {
            $vm = SessionManager::get($sid);
            if (false === $vm) {
                return $response(false, 'Remote desktop session not found', $lock);
            }
            $fpath = Config::get('share_dir').'/'.$vm['hostname'].'/result.zip';
            if (!file_exists($fpath)) {
                return $response(false, 'Result file not found', $lock);
            }
            $ddpath = dirname($dfpath);
            if (!is_dir($ddpath)) {
                if (false === @mkdir($ddpath)) {
                    return $response(false, 'Unexpected Error: failed to create download directory', $lock);
                }
            }
            if (false === @rename($fpath, $dfpath)) {
                return $response(false, 'Unexpected Error: failed to move result file of '.$sid, $lock);
            }

            if (false === SessionManager::updateForDownload($sid)) {
                return $response(false, 'Unexpected Error: failed to update session', $lock);
            }

            SharedDirectoryManager::cleanup($vm['hostname']);

            return $response(true, '', $lock);
        }

        return $response(false, 'Unexpected Error: failed to lock mutex', '');
    }

    /**
     * restart worker virtual machines.
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function restartWorkerVms()
    {
        $remoteDesktop = Config::get('remote_desktop');
        $types = array_keys($remoteDesktop);
        $lock = 'session';
        $response = function ($status, $message, $lock) {
            if ('' !== $lock) {
                Mutex::release($lock);
            }

            return ['status' => $status, 'message' => $message];
        };

        if (Mutex::lock($lock, 10)) {
            foreach ($types as $type) {
                $ips = VirtualMachineManager::restartPowerOffVms($type);
                foreach ($ips as $ip) {
                    SessionManager::deleteByHostname($ip);
                }
            }

            return $response(true, '', $lock);
        }

        return $response(false, 'Unexpected Error: failed to lock mutex', '');
    }

    /**
     * remove expired download files.
     *
     * @param string $dpath
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function removeExpiredDownloadFiles($dpath)
    {
        $lock = 'session';
        $expires = Config::get('download_expires');
        $response = function ($status, $message, $lock) {
            '' !== $lock && Mutex::release($lock);

            return ['status' => $status, 'message' => $message];
        };

        if (Mutex::lock($lock, 10)) {
            $sids = SessionManager::deleteExpiredDownloadSessions($expires);
            foreach ($sids as $sid) {
                $fpath = $dpath.'/'.$sid.'.zip';
                @unlink($fpath);
            }

            return $response(true, '', $lock);
        }

        return $response(false, 'Unexpected Error: failed to lock mutex', '');
    }

    /**
     * terminate ready virtual machines.
     *
     * @param string $hostname
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function terminateReadyVms($hostname)
    {
        $lock = 'session';
        $response = function ($status, $message, $lock) {
            '' !== $lock && Mutex::release($lock);

            return ['status' => $status, 'message' => $message];
        };

        if (Mutex::lock($lock, 10)) {
            $ips = VirtualMachineManager::terminateReadyVms($hostname);
            foreach ($ips as $ip) {
                SessionManager::deleteByHostname($ip);
            }

            return $response(true, '', $lock);
        }

        return $response(false, 'Unexpected Error: failed to lock mutex', '');
    }

    /**
     * prepare desktop connection session.
     *
     * @param string $ip
     * @param string $type
     * @param string $dsize
     *
     * @return bool|string guacamole session id
     */
    private static function prepareSession($ip, $type, $dsize)
    {
        static $ports = [
            'vnc' => 5901,
            'rdp' => 3389,
        ];
        $remoteDesktop = Config::get('remote_desktop');
        $protocol = $remoteDesktop[$type]['protocol'];
        $password = $remoteDesktop[$type]['password'];
        if (!isset($ports[$protocol])) {
            return false;
        }
        $sshConfig = Config::get('ssh');
        $parameters = [];
        if (isset($remoteDesktop[$type]['sftp'])) {
            $sftp = $remoteDesktop[$type]['sftp'];
            $parameters['enable-sftp'] = 'true';
            $parameters['sftp-username'] = $sftp['username'];
            $parameters['sftp-private-key'] = Config::getFileContents($sftp['private_key']);
            $parameters['sftp-passphrase'] = $sftp['passphrase'];
            $parameters['sftp-root-directory'] = $sftp['root_directory'];
        }
        if ('rdp' == $protocol) {
            $parameters['username'] = 'simpf';
            if (preg_match('/^(\d+)x(\d+)$/', $dsize, $matches)) {
                $parameters['width'] = $matches[1];
                $parameters['height'] = $matches[2];
            }
        }
        $conn = SessionManager::create($ip, $protocol, $ports[$protocol], $password, $parameters);
        if (false === $conn) {
            return false;
        }

        return $conn['sid'];
    }
}
