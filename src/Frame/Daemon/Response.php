<?php
namespace Ice\Frame\Daemon;
class Response extends \Ice\Frame\Abs\Response {

    public $stderr;
    public $stdout;


    public function __construct() {
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');
    }

    public function output($content) {
        if (!is_string($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        fwrite($this->stdout, "$content\n");
    }

    public function error($code, $content = '') {
        if (!is_string($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        fwrite($this->stderr, "[$code] $content\n");
        exit($code);
    }
}
