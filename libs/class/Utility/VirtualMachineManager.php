<?php

namespace SimPF\Utility;

use SimPF\Config;
use SimPF\Mutex;
use SimPF\OpenNebula\Client;
use SimPF\OpenNebula\Template;
use SimPF\OpenNebula\TemplatePool;
use SimPF\OpenNebula\VirtualMachine;
use SimPF\OpenNebula\VirtualMachinePool;

class VirtualMachineManager
{
    /**
     * get ready virtual machine ip.
     *
     * @param string $type virtual machine type
     *
     * @return string found virtual machine ip
     */
    public static function getReadyVmIp($type)
    {
        $name = 'opennebula';
        $config = Config::get($name);
        $client = new Client($config);
        $ip = false;
        if (Mutex::lock($name, 10)) {
            $vmpool = new VirtualMachinePool($client);
            $vmis = $vmpool->infoAll();
            foreach ($vmis as $vmi) {
                $vmid = $vmi->getId();
                $vm = VirtualMachine::getInstance($client, $vmid);
                $state = $vm->getState();
                $lcmState = $vm->getLcmState();
                if ('ACTIVE' != $state || 'RUNNING' != $lcmState) {
                    continue;
                }
                $vmType = $vm->getUserTemplate('SIMPF_VM_TYPE');
                if ($type == $vmType) {
                    $vmState = $vm->getUserTemplate('SIMPF_VM_STATE');
                    if ('READY' == $vmState) {
                        $vm->updateUserTemplate(['SIMPF_VM_STATE' => 'RUNNING']);
                        $ip = $vm->getContext('ETH0_IP');
                        break;
                    }
                }
            }
            Mutex::release($name);
        }

        return $ip;
    }

    /**
     * check whether virtual machine is working.
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isWorking($ip)
    {
        $name = 'opennebula';
        $ret = false;
        if (Mutex::lock($name, 10)) {
            $vm = self::findWorkingVm($ip);
            if (false !== $vm) {
                $ret = true;
            }
            Mutex::release($name);
        }

        return $ret;
    }

    /**
     * restore virtual machine state for dispatch error recovery.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return bool
     */
    public static function restoreVmState($ip)
    {
        $name = 'opennebula';
        $ret = false;
        if (Mutex::lock($name, 10)) {
            $vm = self::findWorkingVm($ip);
            if (false !== $vm) {
                $vm->updateUserTemplate(['SIMPF_VM_STATE' => 'READY']);
                $ret = true;
            }
            Mutex::release($name);
        }

        return $ret;
    }

    /**
     * restart power off virtual machines.
     *
     * @param string $type type of virtual machine
     *
     * @return string[]|false
     */
    public static function restartPowerOffVms($type)
    {
        $name = 'opennebula';
        $config = Config::get($name);
        $client = new Client($config);
        $ret = [];
        if (Mutex::lock($name, 10)) {
            $tpl = self::findVmTemplate($type);
            if (false !== $tpl) {
                $vmpool = new VirtualMachinePool($client);
                $vmis = $vmpool->infoAll();
                foreach ($vmis as $vmi) {
                    $vmid = $vmi->getId();
                    $vm = VirtualMachine::getInstance($client, $vmid);
                    $vmType = $vm->getUserTemplate('SIMPF_VM_TYPE');
                    if ($type !== $vmType) {
                        continue;
                    }
                    $state = $vm->getState();
                    if ('POWEROFF' != $state) {
                        continue;
                    }
                    $ip = $vm->getContext('ETH0_IP');
                    $vm->terminate();
                    $tpl->instantiate();
                    $ret[] = $ip;
                }
            }
            Mutex::release($name);
        }

        return $ret;
    }

    /**
     * find working vm.
     *
     * @param string $ip ip address of virtual machine
     *
     * @return VirtualMachine|bool
     */
    private static function findWorkingVm($ip)
    {
        $config = Config::get('opennebula');
        $client = new Client($config);
        $vmpool = new VirtualMachinePool($client);
        $vmis = $vmpool->infoAll();
        $ret = false;
        foreach ($vmis as $vmi) {
            $vmid = $vmi->getId();
            $vm = VirtualMachine::getInstance($client, $vmid);
            $ipAddr = $vm->getContext('ETH0_IP');
            if ($ipAddr !== $ip) {
                continue;
            }
            $state = $vm->getState();
            $lcmState = $vm->getLcmState();
            $vmState = $vm->getUserTemplate('SIMPF_VM_STATE');
            if ('ACTIVE' == $state && 'RUNNING' == $lcmState && 'RUNNING' == $vmState) {
                $ret = $vm;
            }
            break;
        }

        return $ret;
    }

    /**
     * find vm template.
     *
     * @param string $type type of virtual machine template
     *
     * @return Template|bool
     */
    private static function findVmTemplate($type)
    {
        $config = Config::get('opennebula');
        $client = new Client($config);
        $tplpool = new TemplatePool($client);
        $tplis = $tplpool->infoAll();
        $ret = false;
        foreach ($tplis as $tpli) {
            $tplid = $tpli->getId();
            $tpl = Template::getInstance($client, $tplid);
            $name = $tpl->getName();
            if ($name != $type) {
                continue;
            }
            $ret = $tpl;
            break;
        }

        return $ret;
    }
}
