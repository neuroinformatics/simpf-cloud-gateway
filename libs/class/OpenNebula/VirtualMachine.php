<?php

namespace SimPF\OpenNebula;

class VirtualMachine extends PoolElement
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
        $xml = $client->call('vm.info', [$id]);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($res);
        $obj = json_decode($json);

        return new self($client, $obj);
    }

    /**
     * get user template.
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function getUserTemplate($prop)
    {
        return $this->_getProperty(['USER_TEMPLATE', $prop]);
    }

    /**
     * get state.
     *
     * @return string
     */
    public function getState()
    {
        static $state = [
            'INIT',
            'PENDING',
            'HOLD',
            'ACTIVE',
            'STOPPED',
            'SUSPENDED',
            'DONE',
            'FAILED',
            'POWEROFF',
            'UNDEPLOYED',
            'CLONING',
            'CLONING_FAILURE',
        ];
        $value = $this->_getProperty(['STATE']);
        if (null === $value || !isset($state[$value])) {
            return null;
        }

        return $state[$value];
    }

    /**
     * get lcm state.
     *
     * @return string
     */
    public function getLcmState()
    {
        static $state = [
            'LCM_INIT',
            'PROLOG',
            'BOOT',
            'RUNNING',
            'MIGRATE',
            'SAVE_STOP',
            'SAVE_SUSPEND',
            'SAVE_MIGRATE',
            'PROLOG_MIGRATE',
            'PROLOG_RESUME',
            'EPILOG_STOP',
            'EPILOG',
            'SHUTDOWN',
            'CANCEL',
            'FAILURE',
            'CLEANUP_RESUBMIT',
            'UNKNOWN',
            'HOTPLUG',
            'SHUTDOWN_POWEROFF',
            'BOOT_UNKNOWN',
            'BOOT_POWEROFF',
            'BOOT_SUSPENDED',
            'BOOT_STOPPED',
            'CLEANUP_DELETE',
            'HOTPLUG_SNAPSHOT',
            'HOTPLUG_NIC',
            'HOTPLUG_SAVEAS',
            'HOTPLUG_SAVEAS_POWEROFF',
            'HOTPLUG_SAVEAS_SUSPENDED',
            'SHUTDOWN_UNDEPLOY',
            'EPILOG_UNDEPLOY',
            'PROLOG_UNDEPLOY',
            'BOOT_UNDEPLOY',
            'HOTPLUG_PROLOG_POWEROFF',
            'HOTPLUG_EPILOG_POWEROFF',
            'BOOT_MIGRATE',
            'BOOT_FAILURE',
            'BOOT_MIGRATE_FAILURE',
            'PROLOG_MIGRATE_FAILURE',
            'PROLOG_FAILURE',
            'EPILOG_FAILURE',
            'EPILOG_STOP_FAILURE',
            'EPILOG_UNDEPLOY_FAILURE',
            'PROLOG_MIGRATE_POWEROFF',
            'PROLOG_MIGRATE_POWEROFF_FAILURE',
            'PROLOG_MIGRATE_SUSPEND',
            'PROLOG_MIGRATE_SUSPEND_FAILURE',
            'BOOT_UNDEPLOY_FAILURE',
            'BOOT_STOPPED_FAILURE',
            'PROLOG_RESUME_FAILURE',
            'PROLOG_UNDEPLOY_FAILURE',
            'DISK_SNAPSHOT_POWEROFF',
            'DISK_SNAPSHOT_REVERT_POWEROFF',
            'DISK_SNAPSHOT_DELETE_POWEROFF',
            'DISK_SNAPSHOT_SUSPENDED',
            'DISK_SNAPSHOT_REVERT_SUSPENDED',
            'DISK_SNAPSHOT_DELETE_SUSPENDED',
            'DISK_SNAPSHOT',
            'DISK_SNAPSHOT_REVERT',
            'DISK_SNAPSHOT_DELETE',
            'PROLOG_MIGRATE_UNKNOWN',
            'PROLOG_MIGRATE_UNKNOWN_FAILURE',
            'DISK_RESIZE',
            'DISK_RESIZE_POWEROFF',
            'DISK_RESIZE_UNDEPLOYED',
        ];
        $value = $this->_getProperty(['LCM_STATE']);
        if (null === $value || !isset($state[$value])) {
            return null;
        }

        return $state[$value];
    }

    /**
     * get hostname.
     *
     * @return string
     */
    public function getHostname()
    {
        $history = $this->_getProperty(['HISTORY_RECORDS', 'HISTORY']);
        if (null === $history) {
            return '';
        } elseif (is_array($history)) {
            usort($history, function ($a, $b) {
                $aseq = $a->SEQ;
                $bseq = $b->SEQ;

                return $aseq == $bseq ? 0 : ($aseq > $bseq ? 1 : -1);
            });
            $history = array_pop($history);
        }
        if (!property_exists($history, 'HOSTNAME')) {
            return '';
        }

        return $history->HOSTNAME;
    }

    /**
     * update user template.
     *
     * @param array $contents
     * @param bool  $isMerge
     *
     * @return string
     */
    public function updateUserTemplate(array $contents, $isMerge = true)
    {
        $id = $this->getId();
        $xml = '<USER_TEMPLATE>';
        foreach ($contents as $key => $content) {
            $xml .= sprintf('<%s>%s</%s>', $key, htmlspecialchars($content, ENT_XML1, 'UTF-8'), $key);
        }
        $xml .= '</USER_TEMPLATE>';
        $isMerge = $isMerge ? 1 : 0;

        $ret = $this->mClient->call('vm.update', [$id, $xml, $isMerge]);
        if ($ret == $id) {
            foreach ($contents as $key => $content) {
                $this->mInfo->USER_TEMPLATE->$key = $content;
            }
        }

        return $ret;
    }

    /**
     * terminate.
     *
     * @param bool $isHard
     *
     * @return string
     */
    public function terminate($isHard = false)
    {
        $action = $isHard ? 'terminate-hard' : 'terminate';

        return $this->_action($action);
    }

    /**
     * action.
     *
     * @param string $action
     *
     * @return mixed
     */
    private function _action($action)
    {
        $id = $this->getId();

        return $this->mClient->call('vm.action', [$action, $id]);
    }
}
