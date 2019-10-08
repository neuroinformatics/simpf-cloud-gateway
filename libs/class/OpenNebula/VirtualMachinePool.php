<?php

namespace SimPF\OpenNebula;

class VirtualMachinePool extends Pool
{
    const STATE_NOT_DONE = -1;
    const STATE_ALL_VM = -2;

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
     * get all vm in the pool.
     *
     * @return array
     */
    public function infoAll()
    {
        return $this->info(self::FILTER_ALL, -1, -1, self::STATE_NOT_DONE);
    }

    /**
     * get  vm in the pool.
     *
     * @param int $filter
     * @param int $startId
     * @param int $endId
     * @param int $state
     *
     * @return array
     */
    public function info($filter, $startId, $endId, $state)
    {
        return $this->_callInfo('vmpool.info', 'VM', 'VirtualMachine', [$filter, $startId, $endId, $state]);
    }
}
