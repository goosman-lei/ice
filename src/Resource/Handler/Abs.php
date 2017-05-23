<?php
namespace Ice\Resource\Handler;
class Abs {
    protected $conn;
    protected $nodeInfo;
    protected $nodeOptions;
    protected $nodeConfig;
    protected $proxy;

    public function __construct() {
    }

    public function setConn($conn) {
        $this->conn = $conn;
    }

    public function setNodeInfo($nodeInfo) {
        $this->nodeInfo    = $nodeInfo;
        $this->nodeOptions = $nodeInfo['options'];
        $this->nodeConfig  = $nodeInfo['config'];
    }

    public function setProxy($proxy) {
        $this->proxy = $proxy;
    }

    protected function reReconnect() {
        $this->conn = $this->proxy->getRealConn($this->nodeInfo['sn'], $this->nodeInfo, TRUE);
    }
}
