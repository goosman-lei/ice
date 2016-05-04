<?php
namespace Ice\Frame;
/**
 * Silvern 
 * 银弹. ^_^
 * 提供各种通用的空魔术方法. 用于HOOK一些可有可无的对象
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class Silvern {
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
