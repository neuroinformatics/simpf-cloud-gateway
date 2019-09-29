<?php

namespace SimPF\Utility;

use SimPF\Config;
use SimPF\DirectoryUtils;
use SimPF\Downloader;

class SharedDirectoryManager
{
    /**
     * prepare shared directory for dispatch virtual machine.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return ['status' => bool, 'message' => string]
     */
    public static function prepare($ip, $url, $dsize)
    {
        $response = function ($status, $message) {
            return ['status' => $status, 'message' => $message];
        };
        $dpath = self::getPath($ip);
        if (false === $dpath) {
            return $response(false, 'Unexpected Error: Failed to get shared directory.');
        }
        if (!self::cleanup($ip)) {
            return $response(false, 'Unexpected Error: Failed to cleanup shared directory');
        }
        $fpath = self::downloadModel($ip, $url);
        if (false === $fpath) {
            return $response(false, 'Failed to download data');
        }
        if (!self::copyAutorunFiles($ip, $url, $fpath)) {
            self::cleanup($ip);

            return $response(false, 'Unexpected Error: Failed to copy autorun files.');
        }
        $fname = basename($fpath);
        if (!self::putEnvironmentFile($ip, $fname, $dsize)) {
            self::cleanup($ip);

            return $response(false, 'Unexpected Error: Failed to create .env file.');
        }

        return $response(true, '');
    }

    /**
     * get shared directory path.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return bool|string
     */
    public static function getPath($ip)
    {
        if (!self::checkIpAddress($ip)) {
            return false;
        }
        $ret = Config::get('share_dir').'/'.$ip;
        if (!is_dir($ret) || !is_writable($ret)) {
            return false;
        }

        return $ret;
    }

    /**
     * cleanup shared directory.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return bool
     */
    public static function cleanup($ip)
    {
        if (!($dpath = self::getPath($ip))) {
            return false;
        }
        if (!(DirectoryUtils::removeRecursive($dpath))) {
            return false;
        }

        return true;
    }

    /**
     * download file to shared directory.
     *
     * @param string $ip  ip address of virtual machine
     * @param string $url model download url
     *
     * @return string
     */
    public static function downloadModel($ip, $url)
    {
        if (!($dpath = self::getPath($ip))) {
            return false;
        }
        $download = new Downloader(Config::get('downloader'));
        if (!($fpath = $download->download($url, $dpath))) {
            return false;
        }

        return basename($fpath);
    }

    /**
     * copy autorun files.
     *
     * @param string $ip    ip address of virtual machine
     * @param string $url   model download url
     * @param string $fname downloaded file name
     *
     * @return bool
     */
    public static function copyAutorunFiles($ip, $url, $fname)
    {
        if (!($dpath = self::getPath($ip))) {
            return false;
        }
        if ($spath = self::getAutorunPath($url, $dpath.'/'.$fname)) {
            if (!DirectoryUtils::copyRecursive($spath, $dpath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * put environment file.
     *
     * @param string $dpath shared directory path
     * @param string $fname data file name
     * @param string $dsize desktop size
     *
     * @return string|bool autorun directotry path, false if not found
     */
    public static function putEnvironmentFile($ip, $fname, $dsize)
    {
        if (!($dpath = self::getPath($ip))) {
            return false;
        }
        $fpath = $dpath.'/.env';
        $autorun = false;
        $timeout = '300';
        if (file_exists($dpath.'/autorun')) {
            $autorun = @file_get_contents($dpath.'/autorun');
        } elseif (file_exists($dpath.'/autorun.bat')) {
            $autorun = @file_get_contents($dpath.'/autorun.bat');
        }
        if (false !== $autorun) {
            if (preg_match('/X-NIJC-SSVM-TIMEOUT: +(\d+)/', $autorun, $matches)) {
                $timeout = $matches[1];
            }
        }
        $lines = [
            'DATAFILE='.$fname,
            'RESOLUTION='.$dsize,
            'TIMEOUT='.$timeout,
        ];
        if (false === @file_put_contents($fpath, implode("\n", $lines))) {
            return false;
        }

        return true;
    }

    /**
     * get autorun path.
     *
     * @param string $url   model download url
     * @param string $fpath downloaded model file path
     *
     * @return string|bool autorun directotry path, false if not found
     */
    public static function getAutorunPath($url, $fpath)
    {
        $dpath = Config::get('autorun_dir');
        $ret = $dpath.'/v2/'.urlencode($url);
        if (!is_dir($ret)) {
            if (!is_file($fpath)) {
                return false;
            }
            $md5 = md5_file($fpath);
            $ret = $dpath.'/v1/'.$md5;
            if (!is_dir($ret)) {
                return false;
            }
        }

        return $ret;
    }

    /**
     * check ip address.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return bool
     */
    private static function checkIpAddress($ip)
    {
        if (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $matches)) {
            return false;
        }
        for ($i = 1; $i <= 4; ++$i) {
            $num = (int) $matches[$i];
            if (0 >= $num || 255 < $num) {
                return false;
            }
            if ((1 == $i || 4 == $i) && 0 === $num) {
                return false;
            }
        }

        return true;
    }
}
