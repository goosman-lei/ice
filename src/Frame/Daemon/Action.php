<?php
namespace Ice\Frame\Daemon;
abstract class Action {
    protected $request;
    protected $response;
    protected $serverEnv;
    protected $clientEnv;
    
    public function __construct() {
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

    public function output($content) {
        return $this->response->output($content);
    }
    public function error($errno, $content) {
        return $this->response->error($errno, $content);
    }

}
