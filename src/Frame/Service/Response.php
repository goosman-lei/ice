<?php
namespace Ice\Frame\Service;
class Response {
    public $code;
    public $data;
    public $id;

    public function __construct() {
    }

    public function startOb() {
        ob_start(NULL, 1048576, PHP_OUTPUT_HANDLER_CLEANABLE);
    }

    public function output($code, $data = null) {
        $this->code = $code;
        $this->data = $data;

        // process output of user code
        if (!\F_Ice::$ins->runner->mainAppConf['debug']) {
            ob_get_clean();
        } else {
            ob_flush();
        }

        echo $this->serialize();
    }

    public function error($code, $data = null) {
        $this->code = $code;
        $this->data = $data;

        // process output of user code
        if (!\F_Ice::$ins->runner->mainAppConf['debug']) {
            ob_get_clean();
        } else {
            ob_flush();
        }

        echo $this->serialize();
        exit(1);
    }

    public function serialize() {
        return ProtocolJsonV1::encodeRequest($this->code, $this->data, $this->id);
    }
}
