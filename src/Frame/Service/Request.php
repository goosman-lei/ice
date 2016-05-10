<?php
namespace Ice\Frame\Service;
class Request extends \Ice\Frame\Abs\Request {

    public $class;
    public $action;
    public $params;
    public $id;

    public function __construct() {
        parent::__construct();
    }

    public function serialize() {
        return ProtocolJsonV1::encodeRequest($this->class, $this->method, $this->params, $this->id);
    }
}
