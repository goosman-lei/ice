<?php
namespace Ice\Frame\Web;
class Response {

    public $stderr;
    public $stdout;


    public function __construct() {
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');
    }

    public function output($content) {
        fwrite($this->stdout, "$content\n");
    }

    public function error($code, $content) {
        fwrite($this->stderr, "[$code] $content\n");
        exit($code);
    }
}
