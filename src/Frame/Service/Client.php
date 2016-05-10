<?php
namespace Ice\Frame\Service;
class Client {
    protected $handler;
    protected $class;

    public $respHeader = array();

    public function __construct($url, $class = null) {
        $this->class = $class;

        $this->handler = curl_init($url);
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE); // 返回结果
        curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, TRUE); // 支持302跳转
        curl_setopt($this->handler, CURLOPT_MAXREDIRS, 5); // 最大跳转次数
        curl_setopt($this->handler, CURLOPT_NOSIGNAL, TRUE); // Hack不能设置1000ms内超时问题
        curl_setopt($this->handler, CURLOPT_CONNECTTIMEOUT_MS, 100); // 100ms连接超时默认
        curl_setopt($this->handler, CURLOPT_TIMEOUT_MS, 500); // 500ms读写超时默认

        curl_setopt($this->handler, CURLOPT_POST, TRUE);
    }

    public function __call($action, $params) {
        return $this->callArray($this->class, $action, $params);
    }


    public function call($class, $action) {
        $params = func_get_args();
        array_splice($params, 0, 2);

        return $this->callArray($class, $action, $params);
    }

    public function callArray($class, $action, $params) {
        $logData = array(
            'class'  => $class,
            'action' => $action,
        );

        $requestBody = ProtocolJsonV1::encodeRequest($class, $action, $params, \F_Ice::$ins->runner->request->getServiceCallId());
        curl_setopt($this->handler, CURLOPT_POSTFIELDS, $requestBody);

        $responseBody   = curl_exec($this->handler);
        $responseHeader = curl_getinfo($this->handler);

        if (empty($responseBody)) {
            $logData['resp_header'] = $responseHeader;
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_RESP_READ_ERROR);
            return array(
                'code' => \F_ECode::WS_RESP_READ_ERROR,
                'data' => null,
            );
        }

        $response = ProtocolJsonV1::decodeResponse($responseBody);
        if (!is_object($response)) {
            $logData['resp_header'] = $responseHeader;
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, $response);
            return array(
                'code' => $response,
                'data' => null,
            );
        }

        $logData['total_time']  = $responseHeader['total_time'];
        $logData['dns_time']    = $responseHeader['namelookup_time'];
        $logData['conn_time']   = $responseHeader['connect_time'];
        $logData['remote']      = $responseHeader['primary_ip'] . ':' . $responseHeader['primary_port'];
        $logData['code']        = $response->code;
        \F_Ice::$ins->mainApp->logger_ws->info($logData, $response);
        return array(
            'code' => $response->code,
            'data' => $response->data,
        );

    }
}
