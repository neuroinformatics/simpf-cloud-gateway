<?php

namespace SimPF\OpenNebula;

class TemplatePool extends Pool
{
    /**
     * get all template in the pool.
     *
     * @return array
     */
    public function infoAll()
    {
        return $this->info(self::FILTER_ALL, -1, -1);
    }

    /**
     * get template in the pool.
     *
     * @param int $filter
     * @param int $startId
     * @param int $endId
     *
     * @return array
     */
    public function info($filter, $startId, $endId)
    {
        return $this->_callInfo('templatepool.info', 'VMTEMPLATE', 'Template', [$filter, $startId, $endId]);
    }
}
