<?php
namespace Ice\Filter;

class CompileException extends \Exception {
    public function __construct($srcCode, $position, $message) {
        $srcCodeLen = strlen($srcCode);
        $srcCode    = substr($srcCode, 0, $position) . chr(0xE2) . chr(0X83) . chr(0X9C) . substr($srcCode, $position);
        $dstMessage = "[$message] occur at[$position/$srcCodeLen]. source code is:\"$relateCode\"";
        parent::__construct($dstMessage);
    }
}
