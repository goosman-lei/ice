<?php
namespace Ice\Frame\Service\Proxy;
class Common {

    protected $class;

    public function __get($name) {
        $class = $this->class;

        $logData = array(
            'proxy'  => 'common',
            'class'  => $class,
            'name' => $name,
        );

        $serviceNamespace = \F_Ice::$ins->workApp->config->get('app.common_namespace');
        $serviceClass = "\\{$serviceNamespace}\\Service\\" . $class;
        if (!class_exists($serviceClass)) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_UNKONW_SERVICE);
            return array(
                'code' => \F_ECode::WS_PROXY_UNKONW_SERVICE,
                'data' => null,
            );
        }

        $serviceObj = new $serviceClass();
        $serviceObj->setIce(\F_Ice::$ins);

        return $serviceObj->$name;
    }

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
            'proxy'  => 'common',
            'class'  => $class,
            'action' => $action,
        );

        $serviceNamespace = \F_Ice::$ins->workApp->config->get('app.common_namespace');
        $serviceClass = "\\{$serviceNamespace}\\Service\\" . $class;
        if (!class_exists($serviceClass) || !method_exists($serviceClass, $action)) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_UNKONW_SERVICE);
            return array(
                'code' => \F_ECode::WS_PROXY_UNKONW_SERVICE,
                'data' => null,
            );
        }

        $serviceObj = new $serviceClass();
        $serviceObj->setIce(\F_Ice::$ins);

        $serviceObj->message = \Ice\Message\Factory::factory($class, $action, $params);

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
