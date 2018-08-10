<?php
namespace Ice\Resource\Handler;
class Memcached extends Abs {

    public function __call($method, $parameters) {
        $method = strtolower($method);
        return call_user_func_array(array($this->conn, $method), $parameters);
    }
}
