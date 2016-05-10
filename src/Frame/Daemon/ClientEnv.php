<?php
namespace Ice\Frame\Daemon;
class ClientEnv extends \U_Env {

    public function __construct() {
    }

    public function __get($name) {
        if ($name == 'ip') {
            $this->ip = '-';
            return $this->ip;
        }
    }
}
