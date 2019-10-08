<?php

namespace SimPF\OpenNebula;

class Host extends PoolElement
{
    /**
     * create instance.
     *
     * @param Client $client
     * @param int    $id
     *
     * @return Host
     */
    public static function getInstance(Client $client, $id)
    {
        $xml = $client->call('host.info', [$id]);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($res);
        $obj = json_decode($json);

        return new self($client, $obj);
    }

    /**
     * get host name.
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->_getProperty(['NAME']);
    }

    /**
     * get allocated vms.
     *
     * @return array
     */
    public function getVms()
    {
        $ret = $this->_getProperty(['VMS', 'ID']);

        return null === $ret ? [] : array_map('intval', $ret);
    }
}
