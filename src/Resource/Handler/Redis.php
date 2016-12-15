<?php
namespace Ice\Resource\Handler;
class Redis extends Abs {
    /**
     * key
     * 获取redis的key的统一接口, 行为和sprintf一致
     * @param mixed $fmt
     * @static
     * @access public
     * @return string
     */
    public static function key($fmt) {
        $argv = func_get_args();
        return call_user_func_array('sprintf', $argv);
    }

    public function __call($method, $parameters) {
        $method = strtolower($method);
        try {
            $resp = call_user_func_array(array($this->conn, $method), $parameters);
        } catch (\RedisException $e) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'host'    => $this->nodeConfig['host'],
                'port'    => $this->nodeConfig['port'],
                'command' => $method,
                'exception' => array(
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'code'      => $e->getCode(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ),
            ), \F_ECode::REDIS_COMMAND_ERROR);
            $resp = FALSE;
        }
        return $resp;
    }
}
