<?php
namespace Ice\Frame\Web;
class Response {
    // router info
    public $controller;
    public $action;

    public $stderr;
    public $stdout;


    public function __construct() {
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');
    }

    public function output() {
        // process output of user code
        if (!\F_Ice::$ins->runner->mainAppConf['debug']) {
            ob_get_clean();
        } else {
            ob_flush();
        }

        // process template engine render
        $this->bodyBuffer .= $this->tempEngine->render();

        // process headers
        foreach ($this->cookies as $cookie) {
            call_user_func_array('setcookie', $cookie);
        }
        foreach ($this->headers as $header) {
            header($header);
        }

        // process output
        echo $this->bodyBuffer;
    }

    public function error($errno, $data = array()) {
        // process output of user code
        if (!\F_Ice::$ins->runner->mainAppConf['debug']) {
            ob_get_clean();
        } else {
            ob_flush();
        }

        // process template engine render
        $this->bodyBuffer .= $this->tempEngine->renderError($errno, $data);

        // process headers
        foreach ($this->cookies as $cookie) {
            call_user_func_array('setcookie', $cookie);
        }
        foreach ($this->headers as $header) {
            header($header);
        }

        // process output
        echo $this->bodyBuffer;

        exit(1);
    }
}
