<?php

namespace SimPF\OpenNebula;

abstract class Pool
{
    const FILTER_MINE_GROUP = -1;
    const FILTER_ALL = -2;
    const FILTER_MINE = -3;
    const FILTER_GROUP = -4;

    /**
     * client instance.
     *
     * @var Client
     */
    protected $mClient;

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
    abstract public function infoAll();

    /**
     * call info method.
     *
     * @param string $method
     * @param string $tag
     * @param string $klass
     * @param int    $filter
     * @param int    $startId
     * @param int    $endId
     * @param array  $extras
     *
     * @return array
     */
    protected function _callInfo($method, $tag, $klass, $filter, $startId, $endId, array $extras = [])
    {
        $klass = __NAMESPACE__.'\\'.$klass;
        $params = array_merge([$filter, $startId, $endId], $extras);
        $xml = $this->mClient->call($method, $params);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($res);
        $obj = json_decode($json);
        $ret = [];
        if (!property_exists($obj, $tag)) {
            return $ret;
        }
        $objs = is_array($obj->$tag) ? $obj->$tag : [$obj->$tag];
        foreach ($objs as $obj) {
            $ret[] = new $klass($this->mClient, $obj);
        }
        usort($ret, function($a, $b) {
            $aid = $a->getId();
            $bid = $b->getId();
            return $aid === $bid ? 0 : ($aid > $bid ? 1 : -1);
        });

        return $ret;
    }
}
