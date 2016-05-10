<?php
namespace Ice\Frame\Web;
class ClientEnv extends \Ice\Frame\Abs\ClientEnv {

    public function __construct() {
        parent::__construct();
        $this->ip = \U_Ip::getClientIp();
    }
}
