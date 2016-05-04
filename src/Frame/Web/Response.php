<?php
namespace Ice\Frame\Web;
class Response {
    protected $bodyBuffer = '';
    protected $headers    = array();
    protected $cookies    = array();

    protected $tplData = array();

    protected $tempEngine;

    // router info
    public $controller;
    public $action;


    public function __contruct() {
        ob_start(1048576, PHP_OUTPUT_HANDLER_CLEANABLE);
        // init TempEngine
    }

    public function output() {
        // process output of user code
        if (!\F_Ice::$ins->runner->mainAppConf['debug']) {
            ob_end_clean();
        } else {
            ob_flush();
        }

        // process template engine render
        #$this->bodyBuffer .= $this->tempEngine->render($this->controller, $this->action, $this->tplData);

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
        #$this->bodyBuffer .= $this->tempEngine->renderError($this->controller, $this->action, $errno, $data);

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

    public function setTplData($key, $value) {
        $this->tplData[$key] = $value;
    }

    public function setTplDatas($datas) {
        $this->tplData = array_merge($this->tplData, $datas);
    }

    public function cleanBody() {
        $buffer = $this->bodyBuffer;
        $this->bodyBuffer = '';
        return $buffer;
    }

    public function appendBody($string) {
        $this->bodyBuffer .= $string;
    }

    public function setBody($string) {
        $this->bodyBuffer = $string;
    }

    public function addHeader($header) {
        $this->headers[] = $header;
    }

    public function addCookie($name, $value = '', $expire = 0, $path = '',
            $domain = '', $secure = FALSE, $httponly = FALSE) {
        $this->cookies[] = array($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}
