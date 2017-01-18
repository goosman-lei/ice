<?php
namespace Ice\Frame\Service\Proxy;
class Local {
    protected $projectGroup;
    protected $projectName;

    protected $class;

    public function __construct($config, $class = null) {
        $this->projectGroup = $config['project_group'];
        $this->projectName  = $config['project_name'];
        if (isset($class)) {
            $this->class = $class;
        }
    }

    public function _setClass($class) {
        $this->class = $class;
    }

    public function __call($action, $params) {
        return $this->_callArray($this->class, $action, $params);
    }


    public function _call($class, $action) {
        $params = func_get_args();
        array_splice($params, 0, 2);

        return $this->_callArray($class, $action, $params);
    }

    public function _callArray($class, $action, $params) {
        $newWorkApp = \F_App::getServiceApp($this->projectGroup, $this->projectName);
        $oldWorkApp = \F_Ice::$ins->switchWorkApp($newWorkApp);

        $logData = array(
            'proxy'  => 'local',
            'class'  => $class,
            'action' => $action,
        );

        $serviceNamespace = $newWorkApp->config->get('app.namespace');
        $serviceClass = "\\{$serviceNamespace}\\Service\\" . $class;
        if (!class_exists($serviceClass) || !method_exists($serviceClass, $action)) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_UNKONW_SERVICE);
            \F_Ice::$ins->switchWorkApp($oldWorkApp);
            return array(
                'code' => \F_ECode::WS_PROXY_UNKONW_SERVICE,
                'data' => null,
            );
        }

        $serviceObj = new $serviceClass();
        $serviceObj->setIce(\F_Ice::$ins);

        $beginCallTime = microtime(TRUE);
        try {
            $result = call_user_func_array(array($serviceObj, $action), $params);
        } catch (\Exception $e){
            $result = array(
                'code' => \F_ECode::WS_EXCEPTION_RESPONSE,
                'data' => null,
            );
            $logData['exception'] = array(
                'code' => $e->getCode(),
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            );
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_EXCEPTION_RESPONSE);
        }

        $endCallTime   = microtime(TRUE);

        $code = isset($result['code']) ? $result['code'] : \F_ECode::WS_ERROR_RESPONSE;
        $data = isset($result['data']) ? $result['data'] : null;

        $logData['total_time'] = $endCallTime - $beginCallTime;
        $logData['code'] = $code;
        \F_Ice::$ins->mainApp->logger_ws->info($logData);

        $returnArr = array(
            'code' => $code,
            'data' => $data,
        );

        \F_Ice::$ins->switchWorkApp($oldWorkApp);

        return $returnArr;
    }
}
