<?php
namespace Ice\Frame\Runner;
class Web {
    protected $rootPath;

    // input data
    public $serverEnv;
    public $clientEnv;
    public $request;

    // static info
    public $mainAppConf;

    // output data
    public $response;

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
        $this->mainAppConf = \F_Config::getConfig($this->rootPath . '/conf/app.php');
    }

    public function run() {
        $this->initIce();

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

    protected function initIce() {
        $this->ice = \F_Ice::init($this, $this->rootPath);
    }

    protected function setupIce() {
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
        \Ice\Frame\Web\Router\RStatic::route($this->request, $this->response);
    }

    protected function dispatch() {
        $className = "\\{$this->mainAppConf['namespace']}\\Action\\{$this->request->controller}\\{$this->request->action}";

        if (!class_exists($className) || !method_exists($className, 'execute')) {
            \F_Ice::$ins->logger->fatal(array(
                'controller' => $this->controller,
                'action' => $this->action,
                'msg' => 'dispatch error: no class or method',
            ));
            return $this->response->error(\F_ECode::UNKNOWN_URI);
        }

        $actionObj = new $className();
        $actionObj->setRequest($this->request);
        $actionObj->setResponse($this->response);
        $actionObj->setServerEnv($this->serverEnv);
        $actionObj->setClientEnv($this->clientEnv);

        $actionObj->preExecute();
        $actionRet = $actionObj->execute(); 
        $actionObj->postExecute();

        $this->response->output();
    }
}
