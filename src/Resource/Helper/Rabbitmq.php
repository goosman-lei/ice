<?php
namespace Ice\Resource\Helper;

class Rabbitmq {
    protected $resource = null;
    protected $handler = null;

    public function __construct($resource) {
        $this->resource = $resource;
    }

    public function __call($method, $parameters) {
        try {
            $this->initHandler();
            $resp = call_user_func_array(array($this->handler, $method), $parameters);
        }catch(\AMQPException $e){
            $resp = FALSE;
        }
        return $resp;
    }

    protected function initHandler(){
        if(is_null($this->handler)){
            $dsn = 'rabbitmq://'. $this->resource;
            $this->handler = \F_Ice::$ins->workApp->proxy_resource->get($dsn);
        }
    }
}
