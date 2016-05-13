<?php
namespace Ice\Util;
class DMap implements \ArrayAccess {
    public function __construct($datas = array()) {
        if (!is_array($datas)) {
            return ;
        }
        foreach ($datas as $k => $v) {
            $this->$k = $v;
        }
    }
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function &offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    public function merge($datas) {
        if (!is_array($datas) && !($datas instanceof DMap)) {
            return;
        }
        foreach ($datas as $k => $v) {
            $this->$k = $v;
        }
    }
}
