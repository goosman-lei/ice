<?php
namespace Ice\Resource\Handler;
class Abs {
    protected $conn;
    protected $nodeInfo;
    protected $nodeOptions;
    protected $nodeConfig;

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
}
