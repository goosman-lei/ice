<?php
namespace Ice\Frame\Web;
abstract class Action {

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
}
