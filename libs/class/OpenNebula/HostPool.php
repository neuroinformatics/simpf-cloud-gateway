<?php

namespace SimPF\OpenNebula;

class HostPool extends Pool
{
    /**
     * constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->mClient = $client;
    }

    /**
     * get all host in the pool.
     *
     * @return array
     */
    public function infoAll()
    {
        return $this->info();
    }

    /**
     * get host in the pool.
     *
     * @return array
     */
    public function info()
    {
        return $this->_callInfo('hostpool.info', 'HOST', 'Host');
    }
}
