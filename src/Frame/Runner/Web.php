<?php
namespace Ice\Frame\Runner;
class Web {
    public $name = 'web';

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
    public $feature;

    public function __construct($confPath) {
        $this->rootPath = realpath(dirname($confPath) . '/..');
        $this->mainAppConf = \F_Config::getConfig($confPath);
        $this->mainAppConf['runner'] = $this->mainAppConf['runner'][$this->name];
    }

    public function run($mode = 'normal') {
        $this->initIce();

        $this->setupEnv();
        $this->setupRequest();
        $this->setupResponse();

        $this->setupIce($this);

        // 单元测试模式, 不进行路由分发
        if ($mode == 'normal') {
            $this->route();
        }

        $this->initFeature();

        // 单元测试模式, 不进行路由分发
        if ($mode == 'normal') {
            $this->dispatch();
        }
    }

    protected function setupEnv() {
        $serverEnvClass = isset($this->mainAppConf['runner']['frame']['server_env_class'])
                        ? $this->mainAppConf['runner']['frame']['server_env_class']
                        : '\\Ice\\Frame\\Web\\ServerEnv';
        $clientEnvClass = isset($this->mainAppConf['runner']['frame']['client_env_class'])
                        ? $this->mainAppConf['runner']['frame']['client_env_class']
                        : '\\Ice\\Frame\\Web\\ClientEnv';

        $this->serverEnv  = new $serverEnvClass();
        $this->clientEnv  = new $clientEnvClass();
    }

    protected function setupRequest() {
        $requestClass = isset($this->mainAppConf['runner']['frame']['request_class'])
                        ? $this->mainAppConf['runner']['frame']['request_class']
                        : '\\Ice\\Frame\\Web\\Request';
        $this->request    = new $requestClass();
    }

    protected function setupResponse() {
        $responseClass = isset($this->mainAppConf['runner']['frame']['response_class'])
                        ? $this->mainAppConf['runner']['frame']['response_class']
                        : '\\Ice\\Frame\\Web\\Response';
        $this->response = new $responseClass();
    }

    protected function initIce() {
        $this->ice = \F_Ice::init($this, $this->rootPath);
    }

    protected function setupIce() {
        $this->ice->setup();
    }

    protected function __cmpPatternType($p1, $p2) {
        static $patternPriorities = array(
            '==' => 1,
            'i=' => 2,
            '^=' => 3,
            'i^' => 4,
            '$=' => 5,
            'i$' => 6,
            '~=' => 7,
        );
        $t1 = substr(trim($p1), 0, 2);
        $t2 = substr(trim($p2), 0, 2);
        return $patternPriorities[$t1] - $patternPriorities[$t2];
    }

    /**
     * route 
         优先处理特殊路由. 优先级自上而下:
         1. "==": 精确匹配
         2. "i=": 不区分大小写精确匹配
         3. "^=": 精确前缀匹配
         4. "i^": 不区分大小写前缀匹配
         5. "$=": 精确后缀匹配
         6. "i$": 不区分大小写后缀匹配
         7. "~=": 正则匹配
         8. 自定义路由: 逗号分隔, 直到一个路由器返回TRUE
     * @access protected
     * @return void
     */
    protected function route() {
        $routes = $this->mainAppConf['runner']['routes'];
        $defaultRouteClasses = $routes['default'];
        unset($routes['default']);

        // sort with priority
        uksort($routes, array($this, '__cmpPatternType'));

        $routed = FALSE;

        foreach ($routes as $pattern => $rule) {
            $type    = substr(trim($pattern), 0, 2);
            $pattern = trim(substr(trim($pattern), 2));
            $isMatch = FALSE;
            $params  = array();
            switch ($type) {
                case '==':
                    $isMatch = strcmp($pattern, $this->request->uri) == 0;
                    break;
                case 'i=':
                    $isMatch = strcasecmp($pattern, $this->request->uri) == 0;
                    break;
                case '^=':
                    $isMatch = strncmp($pattern, $this->request->uri, strlen($pattern)) == 0;
                    break;
                case 'i^':
                    $isMatch = strncasecmp($pattern, $this->request->uri, strlen($pattern)) == 0;
                    break;
                case '$=':
                    $offset  = max(0, strlen($this->request->uri) - strlen($pattern));
                    $isMatch = strcmp($pattern, substr($this->request->uri, $offset)) == 0;
                    break;
                case 'i$':
                    $offset  = max(0, strlen($this->request->uri) - strlen($pattern));
                    $isMatch = strcasecmp($pattern, substr($this->request->uri, $offset)) == 0;
                    break;
                case '~=':
                    $isMatch = preg_match($pattern, $this->request->uri, $params);
                    break;
                default:
                    break;
            }

            if ($isMatch) {
                $this->request->class   = $rule['class'];
                $this->request->action  = $rule['action'];
                $this->response->class  = $rule['class'];
                $this->response->action = $rule['action'];
                if (isset($rule['params']) && !empty($params)) {
                    foreach ($rule['params'] as $matchKey => $paramName) {
                        $this->request->setParam($paramName, $params[$matchKey]);
                    }
                }
                return ;
            }
        }

        $defaultRouteClasses = explode(',', $defaultRouteClasses);
        foreach ($defaultRouteClasses as $routeClass) {
            $router = new $routeClass();
            if ($router->route($this->request, $this->response)) {
                return ;
            }
        }
    }

    protected function initFeature() {
        $this->feature = new \Ice\Frame\Feature($this->clientEnv);
    }

    public function getAppActionPath(){
        $defaultAction = "\\{$this->mainAppConf['namespace']}\\Action"; 
        return $defaultAction;
    }

    protected function dispatch() {
        try {
            $className = $this->getAppActionPath() . "\\{$this->request->class}\\{$this->request->action}";

            if (!class_exists($className) || !method_exists($className, 'execute')) {
                \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or method',
                ), \F_ECode::UNKNOWN_URI);
                return $this->response->error(\F_ECode::UNKNOWN_URI, array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or method',
                ));
            }

            $tplData = $this->callAction($this->request->class, $this->request->action);

            $this->response->addTplData($tplData);

            //记录请求API日志
            if(isset(\F_Ice::$ins->workApp->logger_webapi)){
                \F_Ice::$ins->workApp->logger_webapi->info(array(
                    'api'    => $this->request->class.'/'.$this->request->action,
                    'query'  => $this->serverEnv->QUERY_STRING,
                    'respTime' => number_format(floatval(microtime(TRUE) -  $this->serverEnv->REQUEST_TIME_FLOAT) * 1000, 2) . 'ms',
                ));
            }

            $this->response->output();
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

    public function callAction($class, $action) {
        $className = $this->getAppActionPath() . "\\{$class}\\{$action}";
        
        $this->request->class  = $class;
        $this->request->action = $action;

        $actionObj = new $className();
        $actionObj->setIce($this->ice);
        $actionObj->setRequest($this->request);
        $actionObj->setResponse($this->response);
        $actionObj->setServerEnv($this->serverEnv);
        $actionObj->setClientEnv($this->clientEnv);
        $actionObj->setFeature($this->feature);

        $actionObj->prevExecute();
        $tplData = $actionObj->execute(); 
        $actionObj->postExecute();

        return $tplData;
    }
}
