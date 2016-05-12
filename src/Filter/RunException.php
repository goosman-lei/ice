<?php
namespace Ice\Filter;
class RunException extends \Exception {
    public function __construct($op, $message) {
        parent::__construct("[op: $op] $message");
    }

}
