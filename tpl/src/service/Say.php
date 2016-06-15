<?php
namespace ${PROJECT_NAMESPACE}\Service;
class Say extends \FS_Service {
    public function hello($name) {
        if ($this->message->isMaster()) {
            echo $this->message->config['server_room'] . ' master' . chr(10);
            $this->message->complete();
        }
        if ($this->message->isSlave()) {
            echo $this->message->config['server_room'] . ' slave' . chr(10);
            $this->message->complete();
        }
        return $this->succ('Hello ' . $name);
    }
}
