<?php
namespace Ice\Frame\Abs;
class ServerEnv extends \U_Env {

    public $hostname;

    protected $_servers;

    public function __construct() {
        $this->hostname = gethostname();
        $this->_servers = $_SERVER;
    }

    public function __get($name) {
        return $this->_servers[$name];
    }
}
