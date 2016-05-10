<?php
namespace Ice\Frame\Web;
class ClientEnv extends \Ice\Frame\Abs\ClientEnv {

    public function __construct() {
        parent::__construct();

        $this->ip = $this->getClientIp();
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
