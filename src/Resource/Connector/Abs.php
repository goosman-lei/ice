<?php
namespace Ice\Resource\Connector;
class Abs {
    public static function getSn($nodeConfig, $nodeOptions){
        return 'none';
    }
    public static function getConn($nodeInfo) {
        return FALSE;
    }
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        return array($nodeConfig, $nodeOptions);
    }
}
