<?php
namespace Ice\Frame\Service;
class Service {
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
}