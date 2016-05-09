<?php
namespace Ice\Frame\Service;
class ServerEnv extends \U_Env {
    protected $_servers;

    public function __construct() {
        $this->_servers = $_SERVER;
    }

    public function getServers() {
        return $this->_servers;
    }

    public function __get($name) {
        if ($name == 'hostname') {
            $this->hostname = gethostname();
            return $this->hostname;
        }
        return $this->_servers[$name];
    }
}
