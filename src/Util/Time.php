<?php
namespace Ice\Util;
class Time {
    public static function now() {
        static $now;
        if (!isset($now)) {
            $now = time();
        }
        return php_sapi_name() === 'cli' ? time() : $now;
    }
}