<?php
namespace Ice\Resource\Connector;
class Redis extends Abs {
    
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        if (!isset($nodeOptions['conn_timeout'])) {
            $nodeOptions['conn_timeout'] = 0.1;
        }
        if (!isset($nodeOptions['read_timeout'])) {
            $nodeOptions['read_timeout'] = 1;
        }
        return array($nodeConfig, $nodeOptions);
    }

    public static function getSn($nodeConfig, $nodeOptions) {
        return sprintf('%s:%s', $nodeConfig['host'], $nodeConfig['port']);
    }

    public static function getConn($nodeInfo) {
        $redis   = new \Redis();
        $options = $nodeInfo['options'];
        $config  = $nodeInfo['config'];

        $status = $redis->pconnect($config['host'], $config['port'], $options['conn_timeout']);
        if (!$status) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'host'   => $config['host'],
                'port'   => $config['port'],
            ), \F_ECode::REDIS_CONN_ERROR);
            return FALSE;
        }
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, floatval($options['read_timeout']));

        return $redis;
    }
}
