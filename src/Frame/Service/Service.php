<?php
namespace Ice\Frame\Service;
class Service {
    // context
    protected $ice;

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