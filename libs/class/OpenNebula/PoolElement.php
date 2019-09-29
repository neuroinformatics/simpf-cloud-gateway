<?php

namespace SimPF\OpenNebula;

abstract class PoolElement
{
    /**
     * client instance.
     *
     * @var Client
     */
    protected $mClient;

    /**
     * vm info.
     *
     * @var \stdClass
     */
    protected $mInfo;

    /**
     * constructor.
     *
     * @param Client   $client
     * @param stdClass $info
     */
    public function __construct(Client $client, \stdClass $info)
    {
        $this->mClient = $client;
        $this->mInfo = $info;
    }

    /**
     * get id.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->_getProperty(['ID']);
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
     * get user id.
     *
     * @return int
     */
    public function getUserId()
    {
        return (int) $this->_getProperty(['UID']);
    }

    /**
     * get group id.
     *
     * @return int
     */
    public function getGroupId()
    {
        return (int) $this->_getProperty(['GID']);
    }

    /**
     * get user name.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->_getProperty(['UNAME']);
    }

    /**
     * get group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->_getProperty(['GNAME']);
    }

    /**
     * get permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return (array) $this->_getProperty(['PERMISSIONS']);
    }

    /**
     * get template.
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function getTemplate($prop)
    {
        return $this->_getProperty(['TEMPLATE', $prop]);
    }

    /**
     * get context.
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function getContext($prop)
    {
        return $this->_getProperty(['TEMPLATE', 'CONTEXT', $prop]);
    }

    /**
     * get property.
     *
     * @param array $props
     *
     * @return mixed
     */
    protected function _getProperty(array $props)
    {
        $obj = $this->mInfo;
        foreach ($props as $prop) {
            if (!property_exists($obj, $prop)) {
                return null;
            }
            $obj = $obj->$prop;
        }

        return $obj;
    }
}
