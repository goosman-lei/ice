<?php
namespace Ice\Frame\Web;
abstract class Action {
    // context
    protected $ice;

    protected $request;
    protected $response;
    protected $serverEnv;
    protected $clientEnv;
    
    public function __construct() {
    }

    public function prevExecute() {
    }
    public function postExecute() {
    }

    abstract public function execute();

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

    public function setIce($ice) {
        $this->ice = $ice;
    }
}
