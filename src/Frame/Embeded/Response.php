<?php
namespace Ice\Frame\Embeded;
class Response extends \Ice\Frame\Abs\Response {
    public function error($errno, $data = array()) {
        $this->code = $code;
        $this->data = $data;

        echo $this->serialize();
        exit(1);
    }
}
