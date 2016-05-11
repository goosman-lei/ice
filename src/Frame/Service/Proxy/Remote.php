<?php
namespace Ice\Frame\Service\Proxy;
class Remote {
    protected $resource;

    protected $class;

    public function __construct($config, $class = null) {
        $this->resource = $config['resource'];
        $this->handler  = \F_Ice::$ins->workApp->proxy_resource->get($this->resource);
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
            'proxy'  => 'remote',
            'class'  => $class,
            'action' => $action,
        );

        $requestBody = \Ice\Frame\Service\ProtocolJsonV1::encodeRequest($class, $action, $params, \F_Ice::$ins->runner->request->getServiceCallId());

        $responseBody = $this->handler->post('/', $requestBody, array(), $responseHeader);

        if (empty($responseBody)) {
            $logData['resp_header'] = $responseHeader;
            \F_Ice::$ins->mainApp->logger_ws->warn($logData, \F_ECode::WS_PROXY_READ_ERROR);
            return array(
                'code' => \F_ECode::WS_PROXY_READ_ERROR,
                'data' => null,
            );
        }

        $response = \Ice\Frame\Service\ProtocolJsonV1::decodeResponse($responseBody);
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
        \F_Ice::$ins->mainApp->logger_ws->info($logData);
        return array(
            'code' => $response->code,
            'data' => $response->data,
        );
    }

}
