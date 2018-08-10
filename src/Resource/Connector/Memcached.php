<?php
namespace Ice\Resource\Connector;
class Memcached extends Abs {
    
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        if (!isset($nodeOptions['conn_timeout'])) {
            $nodeOptions['conn_timeout'] = 100;
        }
        if (!isset($nodeOptions['read_timeout'])) {
            $nodeOptions['read_timeout'] = 1000;
        }
        if (!isset($nodeOptions['write_timeout'])) {
            $nodeOptions['write_timeout'] = 1000;
        }
        return array($nodeConfig, $nodeOptions);
    }

    public static function getSn($nodeConfig, $nodeOptions) {
        return sprintf('%s:%s', $nodeConfig['host'], $nodeConfig['port']);
    }

    public static function getConn($nodeInfo) {
        $memcached = new \Memcached();
        $options   = $nodeInfo['options'];
        $config    = $nodeInfo['config'];

        $memcached->setOption(\Memcached::OPT_COMPRESSION, FALSE);
        if (!empty($options['conn_timeout'])) {
            $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, intval($options['conn_timeout']));
        }
        if (!empty($options['read_timeout'])) {
            $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, intval($options['read_timeout']));
        }
        if (!empty($options['write_timeout'])) {
            $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, intval($options['write_timeout']));
        }

        $memcached->addServer($config['host'], $config['port'], 1);
        return $memcached;
    }
}
