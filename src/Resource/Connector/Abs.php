<?php
namespace Ice\Resource\Connector;
abstract class Abs {
    abstract public static function getSn($nodeConfig, $nodeOptions);
    abstract public static function getConn($nodeInfo);
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        return array($nodeConfig, $nodeOptions);
    }
}
