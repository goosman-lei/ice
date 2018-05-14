<?php
namespace Ice\Resource\Handler;
class Curl extends Abs {
    protected function getFullUrl($path) {
        $host   = $this->nodeConfig['host'];
        $path   = '/' . ltrim($path, '/');
        $scheme = $this->nodeOptions['scheme'];
        $port   = '';
        if (isset($this->nodeConfig['port'])) {
            if (getservbyname($this->nodeOptions['scheme'], 'tcp') != $this->nodeConfig['port']) {
                $port = ':' . $this->nodeConfig['port'];
            }
        }
        if (isset($this->nodeOptions['hostname'])) {
            curl_setopt($this->conn, CURLOPT_HTTPHEADER, array("Host: {$this->nodeOptions['hostname']}"));
        }

        return sprintf('%s://%s%s%s', $scheme, $host, $port, $path);
    }

    public function post($path, $datas = array(), $opts = array(), &$respInfo = array()) {
        $postFields = is_array($datas) ? $datas : (string)$datas;

        $url = $this->getFullUrl($path);

        curl_setopt($this->conn, CURLOPT_URL, $url);

        curl_setopt($this->conn, CURLOPT_POST, TRUE);
        if ($postFields) {
            curl_setopt($this->conn, CURLOPT_POSTFIELDS, $postFields);
        }

        if (is_array($opts) && !empty($opts)) {
            curl_setopt_array($this->conn, $opts);
        }

        $respBody = curl_exec($this->conn);
        $errorCode = curl_errno($this->conn);
        if ($respBody === FALSE && $errorCode !== 0) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'errorCode' => $errorCode,
                'errorMsg'  => curl_error($this->conn),
                'url'       => $url,
            ), \F_ECode::CURL_POST_EXEC_ERROR);
            return FALSE;
        }
        $respInfo = curl_getinfo($this->conn);

        if (intval($respInfo['http_code'] / 100)  != 2) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'respInfo'  => $respInfo,
                'url'       => $url,
            ), \F_ECode::CURL_POST_HTTP_ERROR);
            return FALSE;
        }

        return strval($respBody);
    }

    public function get($path, $datas = array(), $opts = array(), &$respInfo = array()) {
        $queryString = is_array($datas) ? http_build_query($datas) : (string)$datas;

        $url    = $this->getFullUrl($path);
        if ($queryString) {
            $url = $url . '?' . $queryString;
        }

        curl_setopt($this->conn, CURLOPT_URL, $url);

        curl_setopt($this->conn, CURLOPT_HTTPGET, TRUE);

        if (is_array($opts) && !empty($opts)) {
            curl_setopt_array($this->conn, $opts);
        }

        $respBody = curl_exec($this->conn);
        $errorCode = curl_errno($this->conn);
        if ($respBody === FALSE && $errorCode !== 0) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'errorCode' => $errorCode,
                'errorMsg'  => curl_error($this->conn),
                'url'       => $url,
            ), \F_ECode::CURL_GET_EXEC_ERROR);
            return FALSE;
        }
        $respInfo = curl_getinfo($this->conn);

        if ($respInfo['http_code'] != '200') {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'respInfo'  => $respInfo,
                'url'       => $url,
            ), \F_ECode::CURL_GET_HTTP_ERROR);
            return FALSE;
        }

        return strval($respBody);
    }

    public function curlGetJson($path, $datas = array(), $opts = array(), &$respInfo = array(), $params = array()) {
        $path .= '?' . http_build_query($params);
        $result = $this->get($path, $datas, $opts, $respInfo);
        return @json_decode($result, TRUE);
    }

    public function setOpt($k, $v) {
        curl_setopt($this->conn, $k, $v);
    }
}
