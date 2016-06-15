<?php
namespace ${PROJECT_NAMESPACE}\Lib;
class ServiceMessage extends \MSG_Abs {
    public function createId() {
        $proxy = \F_Ice::$ins->workApp->proxy_resource->get($this->config['id_gen_resource']);
        $shardKey = sprintf("%s:%s:%s:%s", $this->config['server_room'], $this->runMode, $this->class, $this->action);
        return $this->id = sprintf("1%08d", $proxy->incr($shardKey));
    }

    public function publishMaster() {
        $proxy = \F_Ice::$ins->workApp->proxy_resource->get($this->config['master_resource']);
        $msgStr = $this->serialize();
        $proxy->produce($msgStr, $this->config['master_exchange'], $this->config['master_routingkey']);
    }

    public function publishSlave() {
        $proxy = \F_Ice::$ins->workApp->proxy_resource->get($this->config['slave_resource']);
        $msgStr = $this->serialize();
        $proxy->produce($msgStr, $this->config['slave_exchange'], $this->config['slave_routingkey']);
    }

    public function complete() {
        $proxy = \F_Ice::$ins->workApp->proxy_resource->get($this->config['status_resource']);
        $msgId = sprintf("%s:%s:%s:%s:%s", $this->config['server_room'], $this->runMode, $this->class, $this->action, $this->id);
        $return = $proxy->set($msgId, 1, array(
            'EX' => $this->config['complete_sign_expire'],
            'NX',
        ));
        // 必须在紧挨着返回前, 调用父类的complete.
        parent::complete();
        return $return;
    }

    public function isCompleted() {
        $proxy = \F_Ice::$ins->workApp->proxy_resource->get($this->config['status_resource']);
        $msgId = sprintf("%s:%s:%s:%s:%s", $this->config['server_room'], $this->runMode, $this->class, $this->action, $this->id);
        return $proxy->get($msgId) == 1;
    }
}