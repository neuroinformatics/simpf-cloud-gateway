<?php

namespace SimPF\OpenNebula;

class Template extends PoolElement
{
    /**
     * create instance.
     *
     * @param Client $client
     * @param int    $id
     *
     * @return VirtualMachine
     */
    public static function getInstance(Client $client, $id)
    {
        $xml = $client->call('template.info', [$id, true]);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($res);
        $obj = json_decode($json);

        return new self($client, $obj);
    }

    /**
     * get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getProperty(['NAME']);
    }

    /**
     * instantiate.
     */
    public function instantiate()
    {
        $id = $this->getId();
        $vmid = $this->mClient->call('template.instantiate', [$id, '', false, '', false]);

        return VirtualMachine::getInstance($this->mClient, $vmid);
    }
}
