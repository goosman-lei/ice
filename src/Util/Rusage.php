<?php
namespace Ice\Util;
class Rusage {

    private static $marks = array();

    private static function getRusage() {
        $rusage = getrusage();
        return array(
            'umem'  => memory_get_usage(), 
            'smem'  => memory_get_usage(TRUE), 
            'rtime' => microtime(TRUE) * 1000000, 
            'ucpu'  => $rusage['ru_utime.tv_sec'] * 1000000 + $rusage['ru_utime.tv_usec'], 
            'scpu'  => $rusage['ru_stime.tv_sec'] * 1000000 + $rusage['ru_stime.tv_usec'], 
        );
    }

    /**
     * mark 
     * 打标记
     * @param mixed $markName 
     * @static
     * @access public
     * @return void
     */
    public static function mark($markName) {
        if (array_key_exists($markName, self::$marks)) {
            return NULL;
        }
        $startUsage = self::getRusage();
        $backTrace = debug_backtrace();
        self::$marks[$markName] = array(
            'startPos'   => $backTrace[0]['file'] . ':' . $backTrace[0]['line'],
            'startRusage' => $startUsage, 
        );
    }

    /**
     * stop 
     * 停止一个标记
     * @param mixed $markName 
     * @static
     * @access public
     * @return void
     */
    public static function stop($markName) {
        if (array_key_exists($markName, self::$marks) && !array_key_exists('endRusage', self::$marks[$markName])) {
            $startRusage = self::$marks[$markName]['startRusage'];
            $endRusage = self::getRusage();
            $backTrace = debug_backtrace();
            self::$marks[$markName]['endRusage'] = $endRusage;
            self::$marks[$markName]['endPos'] = $backTrace[0]['file'] . ':' . $backTrace[0]['line'];
            self::$marks[$markName]['rusage'] = array(
                'rtime' => ($endRusage['rtime'] - $startRusage['rtime']) / 1000,
                'ucpu' => ($endRusage['ucpu'] - $startRusage['ucpu']) / 1000,
                'scpu' => ($endRusage['scpu'] - $startRusage['scpu']) / 1000,
                'smem' => ($endRusage['smem'] - $startRusage['smem']) / 1000000,
                'umem' => ($endRusage['umem'] - $startRusage['umem']) / 1000000,
            );
        }
    }

    public static function get($markName) {
        return self::$marks[$markName]['rusage'];
    }
}
