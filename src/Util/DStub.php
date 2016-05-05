<?php
namespace Ice\Util;
/**
 * DStub 
 * 桩对象
 * 提供各种通用的空魔术方法.
 *   比如: \F_Ice::$ins->mainApp->logger. 框架中需要使用它记录日志. 但是, 应用可以决定是否提供logger, 不提供logger时, 用桩对象代替, 不破坏流程设计.
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class DStub {
    public function __call($name, $arguments) {
    }
    public static function __callStatic($name, $arguments) {
    }
    public function __get($name) {
    }
    public function __set($name, $value) {
    }
    public function __isset($name) {
    }
    public function __unset($name) {
    }
}
