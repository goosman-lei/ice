<?php
namespace Ice\Frame\Service;
class Client {
    protected $handler;

    public $respHeader = array();

    public function __construct($url) {
        $this->handler = curl_init($url);
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE); // 返回结果
        curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, TRUE); // 支持302跳转
        curl_setopt($this->handler, CURLOPT_MAXREDIRS, 5); // 最大跳转次数
        curl_setopt($this->handler, CURLOPT_NOSIGNAL, TRUE); // Hack不能设置1000ms内超时问题
        curl_setopt($this->handler, CURLOPT_CONNECTTIMEOUT_MS, 100); // 100ms连接超时默认
        curl_setopt($this->handler, CURLOPT_TIMEOUT_MS, 500); // 500ms读写超时默认

        curl_setopt($this->handler, CURLOPT_POST, TRUE);
    }

    public function call($class, $method) {
        $logData = array(
            'class'  => $class,
            'method' => $method,
        );

        $params = func_get_args();
        array_splice($params, 0, 2);

        $requestBody = ProtocolJsonV1::encodeRequest($class, $method, $params, \F_Ice::$ins->runner->request->getNextRelayId());
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

        $logData['total_time']  = $responseHeader[CURLINFO_TOTAL_TIME];
        $logData['dns_time']    = $responseHeader[CURLINFO_NAMELOOKUP_TIME];
        $logData['conn_time']   = $responseHeader[CURLINFO_CONNECT_TIME];
        $logData['remote']      = $responseHeader[CURLINFO_PRIMARY_IP] . ':' . $responseHeader[CURLINFO_PRIMARY_PORT];
        $logData['code']        = $response->code;
        \F_Ice::$ins->mainApp->logger_ws->info($logData, $response);
        return array(
            'code' => $response->code,
            'data' => $response->data,
        );
    }
}
