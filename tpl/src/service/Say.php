<?php
namespace ${PROJECT_NAMESPACE}\Service;
/**
 * Say 演示示例
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class Say extends \FS_Service {

    /**
     * 演示跨机房消息通信机制的Service
     * @param string $name 打招呼的人名
     * @error 30032 错误1
     * @error 30033 错误3
     * @return json {
     *     "code": 0,
     *     "data": "Hello xxx",
     * }
     */
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

    /**
     * 演示纯粹的Service
     * @param string $name 打招呼的人名
     * @access public
     * @return json {
     *     "code": 0,
     *     "data": "Hello xxx",
     * }
     */
    public function hello($name) {
        return $this->succ('Hello ' . $name);
    }

}
