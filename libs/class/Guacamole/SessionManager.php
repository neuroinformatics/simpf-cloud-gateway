<?php

namespace SimPF\Guacamole;

use SimPF\Guacamole\Model\Connection;
use SimPF\Guacamole\Model\Parameter;

class SessionManager
{
    /**
     * list connection.
     *
     * @return array
     */
    public static function list()
    {
        $ret = [];
        $connObjs = Connection::all()->sortBy('hostname');
        foreach ($connObjs as $connObj) {
            $sid = $connObj->sid;
            $ret[$sid] = $connObj->toArray();
            $paramObjs = Parameter::where('sid', $sid);
            foreach ($paramObjs as $paramObj) {
                $ret[$sid][$paramObj->name] = $paramObj->value;
            }
        }

        return $ret;
    }

    /**
     * create new connection info.
     *
     * @param string $hostname
     * @param string $protocol
     * @param int    $port
     * @param string $password
     * @param array  $parameters
     *
     * @return array
     */
    public static function create($hostname, $protocol, $port, $password, $parameters = [])
    {
        $sid = bin2hex(openssl_random_pseudo_bytes(16));
        $now = time();
        $conn = [
           'sid' => $sid,
           'hostname' => $hostname,
           'protocol' => $protocol,
           'port' => $port,
           'password' => $password,
           'status' => 'READY',
           'timestamp' => $now,
        ];
        $connObj = Connection::create($conn);
        if (!$connObj->save()) {
            return false;
        }
        foreach ($parameters as $name => $value) {
            $param = [
                'sid' => $sid,
                'name' => $name,
                'value' => $value,
            ];
            $paramObj = Parameter::create($param);
            $paramObj->save();
        }

        return self::get($sid);
    }

    /**
     * get connection info.
     *
     * @param string $sid
     *
     * @return array
     */
    public static function get($sid)
    {
        if (!($connObj = Connection::find($sid))) {
            return false;
        }
        $ret = $connObj->toArray();
        $paramObjs = Parameter::where('sid', $sid)->get();
        foreach ($paramObjs as $paramObj) {
            $ret[$paramObj->name] = $paramObj->value;
        }

        return $ret;
    }

    /**
     * update connection info for download.
     *
     * @param array $info
     *
     * @return bool
     */
    public static function updateForDownload($sid)
    {
        if (!($connObj = Connection::find($sid))) {
            return false;
        }
        Parameter::where('sid', $sid)->delete();
        $now = time();
        $connObj->hostname = '';
        $connObj->protocol = '';
        $connObj->port = 0;
        $connObj->password = '';
        $connObj->status = 'DOWNLOAD';
        $connObj->timestamp = $now;

        return $connObj->save();
    }

    /**
     * get session id from hostname.
     *
     * @param string $hostname
     *
     * @return array
     */
    public static function getSessionId($hostname)
    {
        if (!($connObj = Connection::where('hostname', $hostname)->first())) {
            return false;
        }

        return $connObj->sid;
    }

    /**
     * delete connection by hostname.
     *
     * @return int
     */
    public static function deleteByHostname($hostname)
    {
        return Connection::where('hostname', $hostname)->delete();
    }

    /**
     * delete expired download sessions.
     *
     * @param int $expires
     *
     * @return string[]
     */
    public static function deleteExpiredDownloadSessions($expires)
    {
        $sids = [];
        $now = time();
        $connObjs = Connection::where([
            ['status', 'DOWNLOAD'],
            ['timestamp', '<', $now - $expires],
        ])->get();
        foreach ($connObjs as $connObj) {
            $sids[] = $connObj->sid;
            $connObj->delete();
        }

        return $sids;
    }
}
