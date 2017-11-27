<?php
namespace Ice\Frame\Runner;
class Daemon {
    public $name = 'daemon';

    protected $rootPath;

    // input data
    public $serverEnv;
    public $clientEnv;
    public $request;

    // static info
    public $mainAppConf;

    // output data
    public $response;

    // context
    public $ice;

    public function __construct($confPath) {
        $this->rootPath = realpath(dirname($confPath) . '/..');
        $this->mainAppConf = \F_Config::getConfig($confPath);
        $this->mainAppConf['runner'] = $this->mainAppConf['runner']['daemon'];
    }

    public function run() {
        $this->initIce();

        $this->setupEnv();
        $this->setupRequest();
        $this->setupResponse();

        $this->setupIce($this);

        $this->route();

        $this->dispatch();
    }

    protected function setupEnv() {
        $serverEnvClass = isset($this->mainAppConf['runner']['frame']['server_env_class'])
                        ? $this->mainAppConf['runner']['frame']['server_env_class']
                        : '\\Ice\\Frame\\Daemon\\ServerEnv';
        $clientEnvClass = isset($this->mainAppConf['runner']['frame']['client_env_class'])
                        ? $this->mainAppConf['runner']['frame']['client_env_class']
                        : '\\Ice\\Frame\\Daemon\\ClientEnv';

        $this->serverEnv  = new $serverEnvClass();
        $this->clientEnv  = new $clientEnvClass();
    }

    protected function setupRequest() {
        $requestClass = isset($this->mainAppConf['runner']['frame']['request_class'])
                        ? $this->mainAppConf['runner']['frame']['request_class']
                        : '\\Ice\\Frame\\Daemon\\Request';
        $this->request    = new $requestClass();
    }

    protected function setupResponse() {
        $responseClass = isset($this->mainAppConf['runner']['frame']['response_class'])
                        ? $this->mainAppConf['runner']['frame']['response_class']
                        : '\\Ice\\Frame\\Daemon\\Response';
        $this->response = new $responseClass();
    }

    protected function initIce() {
        $this->ice = \F_Ice::init($this, $this->rootPath);
    }

    protected function setupIce() {
        $this->ice->setup();
    }

    protected function route() {
        $class  = @$this->request->options['class'];
        $action = @$this->request->options['action'];
        if (empty($class) || empty($action)) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'class'  => $this->request->class,
                'action' => $this->request->action,
                'msg'    => 'dispatch error: no class or action',
            ), \F_ECode::ROUTE_ERROR);
            return $this->response->error(\F_ECode::ROUTE_ERROR, array(
                'class'  => $this->request->class,
                'action' => $this->request->action,
                'msg'    => 'dispatch error: no class or action',
            ));
        }

        $class  = ucfirst(strtolower($class));
        $action = ucfirst(strtolower($action));

        $this->request->class   = $class;
        $this->request->action  = $action;
        $this->response->class  = $class;
        $this->response->action = $action;
    }

    protected function dispatch() {
        try {
            $className = "\\{$this->mainAppConf['namespace']}\\Daemon\\{$this->request->class}\\{$this->request->action}";

            if (!class_exists($className) || !method_exists($className, 'execute')) {
                \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or action',
                ), \F_ECode::UNKNOWN_URI);
                return $this->response->error(\F_ECode::UNKNOWN_URI, array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or action',
                ));
            }

            $actionObj = new $className();
            $actionObj->setIce($this->ice);
            $actionObj->setRequest($this->request);
            $actionObj->setResponse($this->response);
            $actionObj->setServerEnv($this->serverEnv);
            $actionObj->setClientEnv($this->clientEnv);

            $actionObj->execute(); 
        } catch (\Exception $e) {
            $error = array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            );
            \F_Ice::$ins->mainApp->logger_comm->fatal($error, \F_ECode::PHP_ERROR);
            $this->response->error(\F_ECode::PHP_ERROR, $error);
        }
    }
}