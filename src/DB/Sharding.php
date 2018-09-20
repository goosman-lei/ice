<?php
namespace Ice\DB;
/**
 * Sharding 
 * 切片. 应用于ShardQueryV2
 * @abstract
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
abstract class Sharding {
    protected $args = [];
    protected $name = '';
    protected $index = '';
    public function __construct($args) {
        $this->args[] = $args;
        // 要使用静态延迟绑定. call_user_func不支持.
        switch (count($args)) {
        case 0:
            $this->name  = static::shardingName();
            $this->index = static::shardingIndex();
        case 1:
            $this->name  = static::shardingName($args[0]);
            $this->index = static::shardingIndex($args[0]);
        case 2:
            $this->name  = static::shardingName($args[0], $args[1]);
            $this->index = static::shardingIndex($args[0], $args[1]);
        case 3:
            $this->name  = static::shardingName($args[0], $args[1], $args[2]);
            $this->index = static::shardingIndex($args[0], $args[1], $args[2]);
        case 4:
            $this->name  = static::shardingName($args[0], $args[1], $args[2], $args[3]);
            $this->index = static::shardingIndex($args[0], $args[1], $args[2], $args[3]);
        case 5:
            $this->name  = static::shardingName($args[0], $args[1], $args[2], $args[3], $args[4]);
            $this->index = static::shardingIndex($args[0], $args[1], $args[2], $args[3], $args[4]);
        }

    }

    public function getArgs() {
        return $this->args;
    }

    public function getName() {
        return $this->name;
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    public function getTableName() {
        return $this->tableName;
    }

    public function getIndex() {
        return $this->index;
    }

    public function pushArgs() {
        $this->args[] = $args;
    }

    abstract public static function shardingName();
    abstract public static function shardingIndex();
}
