<?php
namespace Ice\Frame\Service;
class Service {
    // context
    protected $ice;

    protected $request;
    protected $response;
    protected $serverEnv;
    protected $clientEnv;

    public function setRequest($request) {
        $this->request = $request;
    }
    public function setResponse($response) {
        $this->response = $response;
    }
    public function setServerEnv($serverEnv) {
        $this->serverEnv = $serverEnv;
    }
    public function setClientEnv($clientEnv) {
        $this->clientEnv = $clientEnv;
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
}