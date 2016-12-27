<?php
namespace Ice\Frame\Service;
class Service {
    // context
    protected $ice;

    protected static $iceProxy = NULL;

    public function getIceProxy($libName, $serviceName) {
        if (!isset(self::$iceProxy[$libName][$serviceName])) {
            self::$iceProxy[$libName][$serviceName] = $this->ice->workApp->proxy_service->get($libName, $serviceName);;
        }
        return self::$iceProxy[$libName][$serviceName];
    }

    public function succ($data = null) {
        return array(
            'code' => 0,
            'data' => $data,
        );
    }

    public function error($code, $data = null) {
        return array(
            'code' => $code,
            'data' => $data,
        );
    }

    public function setIce($ice) {
        $this->ice = $ice;
    }

    public static function isSucc($data) {
        return $data['code'] == 0;
    }
}
