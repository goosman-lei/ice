<?php
namespace Ice\Frame\Service;
class ClientEnv extends \U_Env {

    public function __construct() {
    }

    public function __get($name) {
        if ($name == 'ip') {
            $this->ip = $this->getClientIp();
            return $this->ip;
        }
    }

    protected function getClientIp() {
        $clientIp = '';
        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $clientIp = getenv('HTTP_X_FORWARDED_FOR');
            strpos($clientIp, ',') && list($clientIp) = explode(',', $clientIp);
        } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $clientIp = getenv('HTTP_CLIENT_IP');
        } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $clientIp = getenv('REMOTE_ADDR');
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }
        $clientIp = preg_match(';\d{1,3}(\.\d{1,3}){3};', $clientIp) ? $clientIp : '0.0.0.0';

        return $clientIp;
    }

}
