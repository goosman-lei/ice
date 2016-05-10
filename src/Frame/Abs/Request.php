<?php
namespace Ice\Frame\Abs;
class Request {

    // fixed info
    public $id;
    public $requestTime;

    // route result
    public $class;
    public $action;

    // service call id generator
    protected $serviceCallId = 1;

    public function getServiceCallId() {
        return sprintf("%s:%04d", $this->id, $this->serviceCallId ++);
    }

    public function __construct() {
        $this->id = md5(sprintf("%s|%s|%s", gethostname(), posix_getpid(), microtime(TRUE), rand(0, 999999)));
        $this->requestTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : \U_Time::now();
    }
}
