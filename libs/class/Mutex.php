<?php

namespace SimPF;

use Predis\Client;

class Mutex
{
    /**
     * maximum lock time.
     */
    const MAX_LOCK_TIME = 30;

    /**
     * redis.
     *
     * @var \Predis\Client
     */
    private static $mRedis = false;

    /**
     * session.
     *
     * @var string
     */
    private static $mSession = false;

    /**
     * aquire lock.
     *
     * @param string $name
     * @param int    $timeout
     *
     * @return bool
     */
    public static function lock($name, $timeout)
    {
        if (false === self::$mRedis) {
            self::_init();
        }
        $start_time = time();
        $address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        for ($now = time(); $now < $start_time + $timeout; $now = time()) {
            $data = [
                'expires' => $now + self::MAX_LOCK_TIME + 1,
                'address' => $address,
                'session' => self::$mSession,
            ];
            if (!self::_setnx($name, $data)) {
                if ($data2 = self::_get($name)) {
                    if ($data2['expires'] > $now) {
                        sleep(1);
                        continue;
                    }
                    self::release($name);
                    continue;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * release lock.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function release($name)
    {
        if (false === self::$mRedis) {
            self::_init();
        }
        $now = time();
        if (!($data = self::_get($name))) {
            return false;
        }
        if ($data['session'] != self::$mSession && $data['expires'] > $now) {
            return false;
        }

        return self::_del($name);
    }

    /**
     * initialize.
     */
    private static function _init()
    {
        $conn = Config::get('redis');
        self::$mRedis = new Client($conn);
        self::$mSession = bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * get data.
     *
     * @param string $name
     *
     * @return array
     */
    private static function _get($name)
    {
        if (!($data = self::$mRedis->get(self::_prefix($name)))) {
            return null;
        }

        return unserialize($data);
    }

    /**
     * setnx data.
     *
     * @param string $name
     * @param array  $data
     *
     * @return bool
     */
    private static function _setnx($name, array $data)
    {
        return self::$mRedis->setnx(self::_prefix($name), serialize($data));
    }

    /**
     * del data.
     *
     * @param string $name
     *
     * @return bool
     */
    private static function _del($name)
    {
        return self::$mRedis->del(self::_prefix($name));
    }

    /**
     * get prefixed name.
     *
     * @param $name
     */
    private static function _prefix($name)
    {
        return str_replace('\\', '.', __CLASS__).'.'.$name;
    }
}
