<?php
namespace Ice\Frame\Web;
class Response extends \Ice\Frame\Abs\Response {

    protected $bodyBuffer = '';
    protected $headers    = array();
    protected $cookies    = array();

    protected $tplData = array();

    protected $tempEngine;

    public function __construct() {
        parent::__construct();
    }

    protected function initTempEngine() {
        if (isset($this->tempEngine)) {
            return ;
        }
        // read template engine config
        $config = \F_Ice::$ins->runner->mainAppConf['runner']['temp_engine'];
        
        // find current template engine
        $usedTempEngine = $this->findTempEngine($config['routes']);

        // init template engine object
        $enginesConfig    = $config['engines'][$usedTempEngine];
        $adapterClass     = $enginesConfig['adapter'];
        $adapterConfig    = $enginesConfig['adapter_config'];
        $tempEngineConfig = $enginesConfig['temp_engine_config'];
        $this->tempEngine = new $adapterClass($this, $adapterConfig, $tempEngineConfig);
    }

    protected function findTempEngine($config) {
        $lowerClass  = strtolower($this->class);
        $lowerAction = strtolower($this->action);
        if (!isset($config[$lowerClass])) {
            return $config['*'];
        }

        $config = $config[$lowerClass];
        if (!is_array($config)) {
            return $config;
        }

        if (!isset($config[$lowerAction])) {
            return $config['*'];
        }

        return $config[$lowerAction];
    }

    public function output() {
        $this->initTempEngine();

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
        $this->initTempEngine();

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

    public function getTplData() {
        return $this->tplData;
    }

    public function addTplData($datas) {
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
