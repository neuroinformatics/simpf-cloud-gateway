<?php

namespace SimPF\OpenNebula;

class Client
{
    /**
     * client instance.
     *
     * @var \PHPOneAPI\Client
     */
    private $mClient;

    /**
     * constructor.
     *
     * @param array
     */
    public function __construct(array $conn)
    {
        $url = parse_url($conn['endpoint']);
        $is_ssl = (isset($url['schema']) && 'https' == $url['schema']) ? true : false;
        $port = isset($url['port']) ? (int) $url['port'] : 2633;
        $path = isset($url['path']) ? trim($url['path'], '/') : 'RPC2';
        $this->mClient = new \PHPOneAPI\Client($conn['username'], $conn['password'], $url['host'], $is_ssl, $port, $path);
    }

    /**
     * call client api.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function call($method, array $args = [])
    {
        try {
            $ret = $this->mClient->call('one.'.$method, $args);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $ret;
    }

    /**
     * get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->call('system.version');
    }
}
