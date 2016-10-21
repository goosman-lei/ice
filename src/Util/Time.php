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

    /**
     * 判断时间是否是给定的格式 06:30:00
     * @param string $time
     * @return void
     */
    public static function isValidTime($time) {
        $time = trim($time);
        $pattern = ';^(([01]\d)|(2[0-3]))(:[0-5]\d){2}$;';
        return preg_match($pattern, $time);
    }

    /**
     * 判断当前时间是否在指定时间段内
     * @param string $start    06:00:00
     * @param string $end      13:20:00
     * @param string $nowTime  10:00:00 默认为当前时间
     * @static
     * @return TRUE/FALSE
     */
    public static function inTimeSpan($start, $end, $nowTime = NULL) {
        if (!self::isValidTime($start) || !self::isValidTime($end)) {
            return FALSE;
        }

        if (is_null($nowTime) || !self::isValidTime($nowTime)) {
            $nowTime = date("H:i:s", self::now());
        }

        if ($nowTime > $start && $nowTime < $end) {
            return TRUE;
        }
        return FALSE;
    }
}
