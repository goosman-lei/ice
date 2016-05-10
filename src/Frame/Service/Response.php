<?php
namespace Ice\Frame\Service;
class Response extends \Ice\Frame\Abs\Response {

    public $code;
    public $data;
    public $id;

    public function __construct() {
        parent::__construct();
    }

    public function output($code, $data = null) {
        $this->code = $code;
        $this->data = $data;

        echo $this->serialize();
    }

    public function error($code, $data = null) {
        $this->code = $code;
        $this->data = $data;

        echo $this->serialize();
        exit(1);
    }

    public function serialize() {
        return ProtocolJsonV1::encodeResponse($this->code, $this->data, $this->id);
    }
}
