<?php
namespace Ice\Frame\Runner;
class Web {
    protected $rootPath;

    // input data
    public $serverEnv;
    public $clientEnv;
    public $request;

    // static info
    protected $mainAppConf;

    // output data
    public $response;

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
        $this->mainAppConf = \F_Config::getConfig($this->rootPath . '/conf/app.php');
    }

    public static function run() {
        $this->setupEnv();
        $this->setupRequest();
        $this->setupResponse();
        $this->clearInput();

        $this->setupIce($this);

        $this->route();

        $this->dispatch();
    }

    protected function setupEnv() {
        $serverEnvClass = isset($this->mainAppConf['frame']['server_env_class'])
                        ? $this->mainAppConf['frame']['server_env_class']
                        : '\\Ice\\Frame\\Web\\ServerEnv';
        $clientEnvClass = isset($this->mainAppConf['frame']['client_env_class'])
                        ? $this->mainAppConf['frame']['client_env_class']
                        : '\\Ice\\Frame\\Web\\ClientEnv';

        $this->serverEnv  = new $serverEnvClass();
        $this->clientEnv  = new $clientEnvClass();
    }

    protected function setupRequest() {
        $requestClass = isset($this->mainAppConf['frame']['request_class'])
                        ? $this->mainAppConf['frame']['request_class']
                        : '\\Ice\\Frame\\Web\\Request';
        $this->request    = new $requestClass();
    }

    protected function setupResponse() {
        $responseClass = isset($this->mainAppConf['frame']['response_class'])
                        ? $this->mainAppConf['frame']['response_class']
                        : '\\Ice\\Frame\\Web\\Response';
        $this->response = new $responseClass();
    }

    protected function setupIce() {
        $this->ice = new \F_Ice($this->rootPath);
        $this->ice->setup();
    }

    protected function clearInput() {
        $_SERVER = array();
        $_GET    = array();
        $_POST   = array();
        $_FILES  = array();
        $_COOKIE = array();
    }

    protected function route() {
        // 路由暂不扩充
        \Ice\Frame\Router\RStatic::route($this->request);
    }

    protected function dispatch() {
        $ice = \F_Ice::$ins;

        $className = "\\{$ice->mainAppConf['namespace']}\\Action\\{$this->controller}\\{$this->action}";

        if (!class_exists($className) || !method_exists($className, 'execute')) {
            $ice->logger->fatal(array(
                'controller' => $this->controller,
                'action' => $this->action,
                'msg' => 'dispatch error: no class or method',
            ));
            return $this->response->error(\F_ECode::UNKNOWN_URI);
        }

        $actionObj = new $className();

        $actionResp = $actionObj->execute(); 

        $this->response->render($actionResp);
    }
}
