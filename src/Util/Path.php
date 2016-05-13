<?php
namespace Ice\Util;
class Path {

    /**
     * absPath 
     * 不检测是否真实存在, 不解软链, 仅字符串的路径转换
     * 应用场景: 确定目录的正确性, 仅将其转换为可读性更强的形式
     * @param string $path 
     * @param mixed $cwd 
     * @static
     * @access public
     * @return void
     */
    public static function absPath($path, $cwd = null) {
        $path = strval($path);

        // 修正当前路径
        if (is_null($cwd)) {
            $cwd = getcwd();
        }
        $cwd = strval($cwd);

        // 修正为绝对路径
        if ($path[0] !== '/') {
            $path = $cwd . '/' . $path;
        }

        // 删除多余目录分隔符
        $path      = preg_replace(';/+;', '/', $path);

        // 解析.和..的特殊目录
        $path_info = explode('/', $path);
        $target    = array();
        foreach ($path_info as $ele) {
            if ($ele === '.') {
                continue;
            } else if ($ele === '..') {
                array_pop($target);
            } else {
                array_push($target, $ele);
            }
        }

        return implode('/', $target);
    }

}
