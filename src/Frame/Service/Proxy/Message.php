<?php
namespace Ice\Frame\Service\Proxy;
class Message {

    public function __construct() {
    }

    public function call($runMode, $message) {
        $logData = array(
            'proxy'    => 'message',
            'run_mode' => $runMode,
            'message'  => $message,
        );

        $messageObj = \Ice\Message\Factory::unserialize($message, $runMode);
        if (empty($messageObj)) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_MESSAGE_ERROR);
            return array(
                'code' => \F_ECode::WS_PROXY_MESSAGE_ERROR,
                'data' => null,
            );
        }

        $class = $messageObj->class;
        $action = $messageObj->action;
        $params = $messageObj->params;

        $logData['class']      = $class;
        $logData['action']     = $action;
        $logData['message_id'] = $messageObj->id;

        if ($messageObj->isCompleted()) {
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_MESSAGE_REPEAT);
            return array(
                'code' => \F_ECode::WS_PROXY_MESSAGE_REPEAT,
                'data' => null,
            );
        }

        $serviceNamespace = \F_Ice::$ins->workApp->config->get('app.namespace');
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

        $serviceObj->message = $messageObj;

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
