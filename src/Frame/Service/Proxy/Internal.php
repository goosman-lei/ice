<?php
namespace Ice\Frame\Service\Proxy;
class Internal {
    protected $projectGroup;
    protected $projectName;

    protected $class;

    public function __construct($config, $class = null) {
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
        $logData = array(
            'proxy'  => 'internal',
            'class'  => $class,
            'action' => $action,
        );

        $serviceNamespace = \F_Ice::$ins->workApp->config->get('app.namespace');
        $serviceClass = "\\{$serviceNamespace}\\Service\\" . ucfirst(strtolower($class));
        if (!class_exists($serviceClass) || !method_exists($serviceClass, $action)) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_UNKONW_SERVICE);
            return array(
                'code' => \F_ECode::WS_PROXY_UNKONW_SERVICE,
                'data' => null,
            );
        }

        $serviceObj = new $serviceClass();
        $serviceObj->setIce(\F_Ice::$ins);

        $beginCallTime = microtime(TRUE);
        $result = call_user_func_array(array($serviceObj, $action), $params);
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

        return $returnArr;
    }

}
