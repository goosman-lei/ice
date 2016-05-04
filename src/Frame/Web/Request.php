<?php
namespace Ice\Frame\Web;
class Request {

    protected $_params;
    protected $_gets;
    protected $_posts;
    protected $_cookies;
    protected $_files;
    protected $_body;

    public $requestTime;

    // router info
    public $controller;
    public $action;

    public $uri;
    public $originalUri;

    public function __construct() {
        $this->_params  = array_merge($_GET, $_POST);
        $this->_gets    = $_GET;
        $this->_posts   = $_POST;
        $this->_cookies = $_COOKIE;
        $this->_files   = $_FILES;
        $this->_body    = file_get_contents('php://input');

        $this->requestTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : \U_Time::now();

        $this->initBaseUri();
    }

    public function getParams() {
        return $this->_params;
    }

    public function getQueries() {
        return $this->_gets;
    }

    public function getPosts() {
        return $this->_posts;
    }

    public function getCookies() {
        return $this->_cookies;
    }

    public function getFiles() {
        return $this->_files;
    }

    public function getParam($name, $default = null) {
        return isset($this->_params[$name]) ? $this->_params[$name] : $default;
    }

    public function getQuery($name, $default = null) {
        return isset($this->_gets[$name]) ? $this->_gets[$name] : $default;
    }

    public function getPost($name, $default = null) {
        return isset($this->_posts[$name]) ? $this->_posts[$name] : $default;
    }

    public function getCookie($name, $default = null) {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : $default;
    }

    public function getFile($name, $default = null) {
        return isset($this->_files[$name]) ? $this->_files[$name] : $default;
    }

    public function getBody() {
        return $this->_body;
    }

    protected function initBaseUri() {
        $requestUri     = $_SERVER['REQUEST_URI'];
        $this->originalUri = $requestUri;

        // strip repeat "/"
        $requestUri = preg_replace(';/{2,};', '/', $requestUri);
        $requestUri = '/' . trim($requestUri, '/');

        // allow prefix /index.php
        $scriptFilePath = $_SERVER['SCRIPT_FILENAME'];
        $scriptFname    = basename($scriptFilePath);
        if (strpos($requestUri, "/$scriptFname") === 0) {
            $requestUri = '/' . ltrim(strval(substr($requestUri, strlen("/$scriptFname"))), '/');
        }

        // omit $baseUri
        $mainAppConf = \F_Ice::$ins->runner->mainAppConf;
        if (isset($mainAppConf['baseUri'])) {
            $baseUri = $mainAppConf['baseUri'];
            $baseUri = preg_replace(';/{2,};', '/', $baseUri);
            $baseUri = rtrim($baseUri, '/');

            if (strpos($requestUri, $baseUri . '/') === 0) {
                $requestUri = '/' . ltrim(substr($requestUri, strlen($baseUri)), '/');
            }
        }

        $this->uri = $requestUri;
    }

}
