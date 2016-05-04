<?php
namespace Ice\Frame\Web;
class ServerEnv extends \U_Env {
    protected $_servers;

    public function __construct() {
        $this->_servers = $_SERVER;
    }

    public function getServers() {
        return $this->_servers;
    }

    public function __get($name) {
        return $this->_servers[$name];
    }
}
