<?php
namespace Ice\Frame\Service;
class ProtocolJsonV1 {
    const VERSION = 1;

    public static function encodeRequest($class, $action, $params, $reqId) {
        return json_encode(array(
            'version' => self::VERSION,
            'class'   => $class,
            'action'  => $action,
            'params'  => $params,
            'id'      => $reqId,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function decodeRequest($input) {
        $datas = json_decode($input, TRUE);
        if (!is_array($datas)) {
            return \F_ECode::WS_REQ_PARSE_ERROR;
        }
        if (!isset($datas['version']) || $datas['version'] != self::VERSION) {
            return \F_ECode::WS_REQ_VERSION_ERROR;
        }
        if (!isset($datas['class']) || !isset($datas['action']) || !isset($datas['id'])) {
            return \F_ECode::WS_REQ_PROTOCOL_ERROR;
        }
        if (!$datas['class'] || !$datas['action'] || !$datas['id']) {
            return \F_ECode::WS_REQ_PROTOCOL_ERROR;
        }
        if (!isset($datas['params']) || !is_array($datas['params'])) {
            $datas['params'] = array();
        }

        $request = new Request();
        $request->class  = @$datas['class'];
        $request->action = @$datas['action'];
        $request->params = @$datas['params'];
        $request->id     = @$datas['id'];

        return $request;
    }

    public static function encodeResponse($code, $data, $reqId) {
        return json_encode(array(
            'version' => self::VERSION,
            'code'    => $code,
            'data'    => $data,
            'id'      => $reqId,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function decodeResponse($input) {
        $datas = json_decode($input, TRUE);
        if (!is_array($datas)) {
            return \F_ECode::WS_RESP_PARSE_ERROR;
        }
        if (!isset($datas['version']) || $datas['version'] != self::VERSION) {
            return \F_ECode::WS_RESP_VERSION_ERROR;
        }
        if (!isset($datas['code'])) {
            return \F_ECode::WS_RESP_PROTOCOL_ERROR;
        }

        $response = new Response();
        $response->code  = @$datas['code'];
        $response->data  = @$datas['data'];
        $response->reqId = @$datas['reqId'];

        return $response;
    }

}
