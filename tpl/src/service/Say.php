<?php
namespace ${PROJECT_NAMESPACE}\Service;
class Say extends \FS_Service {

    // 演示跨机房消息通信机制的Service
    public function message($name) {
        if ($this->message->isMaster()) {
            echo $this->message->config['server_room'] . ' master[' . $this->message->id . ']' . chr(10);
            $this->message->complete();
        }
        if ($this->message->isSlave()) {
            echo $this->message->config['server_room'] . ' slave[' . $this->message->id . ']' . chr(10);
            $this->message->complete();
        }
        return $this->succ('Hello ' . $name);
    }

    public function hello($name) {
        return $this->succ('Hello ' . $name);
    }

}
