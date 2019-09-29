<?php

namespace SimPF\XooNIps3;

use PhpXmlRpc\Request;
use PhpXmlRpc\Value;

class Client
{
    /**
     * client instance.
     *
     * @var PhpXmlRpc\Client
     */
    protected $mClient;

    /**
     * session string.
     *
     * @var string
     */
    protected $mSession = '';

    /**
     * fault code.
     *
     * @var int
     */
    protected $mFaultCode = 0;

    /**
     * failt string.
     *
     * @var string
     */
    protected $mFaultString = '';

    /**
     * extra errors.
     *
     * @var array
     */
    protected $mExtraErrors = [];

    /**
     * constructor.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->mClient = new \PhpXmlRpc\Client($url);
    }

    /**
     * set credentials.
     *
     * @param string $uname
     * @param string $pass
     *
     * @return bool
     */
    public function setHttpCredentials($uname, $pass)
    {
        return $this->mClient->setCredentials($uname, $pass);
    }

    /**
     * set proxy.
     *
     * @param string $name
     * @param int    $port
     * @param string $uname
     * @param string $pass
     *
     * @return bool
     */
    public function setHttpProxy($host, $port, $uname = '', $pass = '')
    {
        return $this->mClient->setProxy($host, $port, $uname, $pass);
    }

    /**
     * login.
     *
     * @param string $user
     * @param string $pass
     *
     * @return bool
     */
    public function login($user, $pass)
    {
        $params = [
            new Value($user, 'string'),
            new Value($pass, 'string'),
        ];
        $request = new Request('XooNIps.login', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $this->mSession = $this->_decodeValue($value);

        return true;
    }

    /**
     * logout.
     *
     * @return bool
     */
    public function logout()
    {
        $params = [
            new Value($this->mSession, 'string'),
        ];
        $request = new Request('XooNIps.logout', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $mes = $this->_decodeValue($value);
        if ('logged out' != $mes) {
            $this->mFaultCode = 999;
            $this->mFaultString = 'Unexpected message returned :'.$mes;

            return false;
        }

        return true;
    }

    /**
     * get item.
     *
     * @param string $itemId
     * @param string $idType
     *
     * @return array|bool
     */
    public function getItem($itemId, $idType = 'item_id')
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemId, 'string'),
            new Value($idType, 'string'),
        ];
        $request = new Request('XooNIps.getItem', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $item = $this->_decodeValue($value);

        return $item;
    }

    /**
     * get simple items.
     *
     * @param array  $itemIds
     * @param string $idType
     *
     * @return array|bool
     */
    public function getSimpleItems($itemIds, $idType = 'item_id')
    {
        $itemIdValues = [];
        foreach ($itemIds as $itemId) {
            $itemIdValues[] = new Value($itemId, 'string');
        }
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemIdValues, 'array'),
            new Value($idType, 'string'),
        ];
        $request = new Request('XooNIps.getSimpleItems', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $item = $this->_decodeValue($value);

        return $item;
    }

    /**
     * put item.
     *
     * @param array $itemValue
     * @param array $filesValue
     *
     * @return int|bool
     */
    public function putItem($itemValue, $filesValue)
    {
        $params = [
            new Value($this->mSession, 'string'),
            $itemValue,
            $filesValue,
        ];
        $request = new Request('XooNIps.updateItem', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemId = $this->_decodeValue($value);

        return $itemId;
    }

    /**
     * update item.
     *
     * @param array $itemValue
     *
     * @return int|bool
     */
    public function updateItem($itemValue)
    {
        $params = [
            new Value($this->mSession, 'string'),
            $itemValue,
        ];
        $request = new Request('XooNIps.updateItem', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemId = $this->_decodeValue($value);

        return $itemId;
    }

    /**
     * remove item.
     *
     * @param string $itemId
     * @param string $idType
     *
     * @return int|bool
     */
    public function removeItem($itemId, $idType = 'item_id')
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemId, 'string'),
            new Value($idType, 'string'),
        ];
        $request = new Request('XooNIps.removeItem', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemId = $this->_decodeValue($value);

        return $itemId;
    }

    /**
     * get file.
     *
     * @param int  $fileId
     * @param bool $agree
     *
     * @return array|bool
     */
    public function getFile($fileId, $agreement)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($fileId, 'int'),
            new Value($agreement, 'boolean'),
        ];
        $request = new Request('XooNIps.getFile', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $file = $this->_decodeValue($value);

        return $file;
    }

    /**
     * update file.
     *
     * @param int    $itemId
     * @param string $idType
     * @param string $fieldName
     * @param string $fileValue
     *
     * @return int|bool
     */
    public function updateFile($itemId, $idType, $fieldName, $fileValue)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemId, 'string'),
            new Value($idType, 'string'),
            new Value($fieldName, 'string'),
            $fileValue,
        ];
        $request = new Request('XooNIps.updateFile', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $fileId = $this->_decodeValue($value);

        return $fileId;
    }

    /**
     * remove file.
     *
     * @param int $fileId
     *
     * @return int|bool
     */
    public function removeFile($fileId)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($fileId, 'int'),
        ];
        $request = new Request('XooNIps.removeFile', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $fileId = $this->_decodeValue($value);

        return $fileId;
    }

    /**
     * get root index.
     *
     * @param string $open_level
     *
     * @return array|bool
     */
    public function getRootIndex($open_level)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($open_level, 'string'),
        ];
        $request = new Request('XooNIps.getRootIndex', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $index = $this->_decodeValue($value);

        return $index;
    }

    /**
     * get index.
     *
     * @param int $indexId
     *
     * @return array|bool
     */
    public function getIndex($indexId)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($indexId, 'int'),
        ];
        $request = new Request('XooNIps.getIndex', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $index = $this->_decodeValue($value);

        return $index;
    }

    /**
     * get child indexes.
     *
     * @param int $indexId
     *
     * @return array|bool
     */
    public function getChildIndexes($indexId)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($indexId, 'int'),
        ];
        $request = new Request('XooNIps.getChildIndex', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $indexes = $this->_decodeValue($value);

        return $indexes;
    }

    /**
     * search item.
     *
     * @param string $query
     * @param int    $start
     * @param int    $limit
     * @param string $sort
     * @param string $order
     *
     * @return array|bool
     */
    public function searchItem($query, $start = 0, $limit = 20, $sort = 'title', $order = 'asc')
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($query, 'string'),
            new Value($start, 'int'),
            new Value($limit, 'int'),
            new Value($sort, 'string'),
            new Value($order, 'string'),
        ];
        $request = new Request('XooNIps.searchItem', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $results = $this->_decodeValue($value);

        return $results;
    }

    /**
     * get item types.
     *
     * @return array|bool
     */
    public function getItemtypes()
    {
        $params = [
            new Value($this->mSession, 'string'),
        ];
        $request = new Request('XooNIps.getItemtypes', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemtypes = $this->_decodeValue($value);

        return $itemtypes;
    }

    /**
     * get item type.
     *
     * @param int $itemtypeId
     *
     * @return array|bool
     */
    public function getItemtype($itemtypeId)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemtypeId, 'int'),
        ];
        $request = new Request('XooNIps.getItemtype', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemtype = $this->_decodeValue($value);

        return $itemtype;
    }

    /**
     * get preferences.
     *
     * @return array|bool
     */
    public function getPreference()
    {
        $params = [
            new Value($this->mSession, 'string'),
        ];
        $request = new Request('XooNIps.getPreference', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $preferences = $this->_decodeValue($value);

        return $preferences;
    }

    /**
     * update item2.
     *
     * @param array $itemValue
     * @param array $fileValue
     * @param array $deleteItemIds
     *
     * @return array|bool
     */
    public function updateItem2($itemValue, $fileValue, $deleteItemIds)
    {
        $itemIdValues = [];
        foreach ($deleteItemIds as $itemId) {
            $itemIdValues[] = new Value($itemId, 'int');
        }
        $params = [
            new Value($this->mSession, 'string'),
            $itemValue,
            $fileValue,
            new Value($itemIdValues, 'array'),
        ];
        $request = new Request('XooNIps.updateItem2', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $itemId = $this->_decodeValue($value);

        return $itemId;
    }

    /**
     * get file meta.
     *
     * @param int $fileId
     *
     * @return array|bool
     */
    public function getFileMetadata($fileId)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($fileId, 'int'),
        ];
        $request = new Request('XooNIps.getFileMetadata', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $metadata = $this->_decodeValue($value);

        return $metadata;
    }

    /**
     * get file meta.
     *
     * @param array $itemIds
     *
     * @return array|bool
     */
    public function getIndexPathNames($indexIds)
    {
        $indexIdValues = [];
        foreach ($indexIds as $indexId) {
            $indexIdValues[] = new Value($indexId, 'int');
        }
        $params = [
            new Value($this->mSession, 'string'),
            new Value($indexIdValuess, 'array'),
        ];
        $request = new Request('XooNIps.getIndexPathNames', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $indexPaths = $this->_decodeValue($value);

        return $indexPaths;
    }

    /**
     * get item permission.
     *
     * @param int    $itemId
     * @param string $idType
     *
     * @return array|bool
     */
    public function getItemPermission($itemId, $idType)
    {
        $params = [
            new Value($this->mSession, 'string'),
            new Value($itemId, 'string'),
            new Value($idType, 'string'),
        ];
        $request = new Request('XooNIps.getItemPermission', $params);
        if (!($value = $this->_send($request))) {
            return false;
        }
        $perm = $this->_decodeValue($value);

        return $perm;
    }

    /**
     * dump error.
     */
    public function dumpError()
    {
        if (0 == $this->mFaultCode) {
            echo "No Errors\n";

            return;
        }
        echo 'Error('.$this->mFaultCode.') : "'.$this->mFaultString.'"'."\n";
    }

    /**
     * send method.
     *
     * @param array $request
     *
     * @return xxxxx?
     */
    public function _send($request)
    {
        $this->mFaultCode = 0;
        $this->mFaultString = '';
        $this->mExtraErrors = [];
        $res = $this->mClient->send($request);
        $this->mFaultCode = $res->faultCode();
        if (0 != $this->mFaultCode) {
            $this->mFaultString = $res->faultString();
            if (106 == $this->mFaultCode) {
                // xoonips error
                $this->_setXoonipsError($this->mFaultString);
            }

            return false;
        }

        return $res->value();
    }

    /**
     * decode value.
     *
     * @param object $value
     *
     * @return mixed
     */
    public function _decodeValue($value)
    {
        $type = $value->kindOf();
        $ret = null;
        switch ($type) {
            case 'array':
            case 'struct':
                $it = $value->getIterator();
                foreach ($it as $key => $childValue) {
                    $ret[$key] = $this->_decodeValue($childValue);
                }
                break;
            case 'scalar':
                $ret = $value->scalarval();
        }

        return $ret;
    }

    /**
     * set xoonips error.
     *
     * @param string $str
     *
     * @return bool
     */
    public function _setXoonipsError($str)
    {
        $xoonips_errors = [
            100 => 'uncategolized error',
            101 => 'invalid session',
            102 => 'failed to login',
            103 => 'access forbidden',
            104 => 'contents not found',
            105 => 'incomplete required parameters',
            106 => 'missing arguments',
            107 => 'too meny arguments',
            108 => 'invalid argument type',
            109 => 'internal server error',
        ];
        if (!preg_match("/^(Method response error)\n(.*)/", $str, $matches)) {
            return false;
        }
        $messages[] = $matches[1];
        $this->mExtraErrors = @unserialize($matches[2]);
        foreach ($this->mExtraErrors as $err) {
            $mes = ''.$err['code'].' - ';
            $mes .= isset($xoonips_errors[$err['code']]) ? $xoonips_errors[$err['code']] : 'unexpected error';
            if ('' != $err['extra']) {
                $mes .= ' : '.$err['extra'];
            }
            $messages[] = $mes;
        }
        $this->mFaultString = implode("\n", $messages);

        return true;
    }
}
