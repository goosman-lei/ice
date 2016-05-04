<?php
namespace Ice\Frame;
class Config {

    protected $confArr;

    public function __construct($rootPath) {
        $this->confArr = self::loadConfig($rootPath);
    }

    /**
     * get 
     * 获取配置项
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function get($key) {
        $keyEles = explode('.', $key);
        $confVal = $this->confArr;
        foreach ($keyEles as $keyEle) {
            if (!isset($confVal[$keyEle])) {
                return NULL;
            }
            $confVal = $confVal[$keyEle];
        }
        return $confVal;
    }

    /**
     * set 
     * 覆盖设置一个配置项
     * @param mixed $key 
     * @param mixed $val 
     * @access public
     * @return void
     */
    public function set($key, $val) {
        $keyEles = explode('.', $key);
        $confRef = &$this->confArr;
        foreach ($keyEles as $keyEle) {
            if (!is_array($confRef[$keyEle])) {
                $confRef[$keyEle] = array();
            }
            $confRef = &$confRef[$keyEle];
        }
        $confRef = $val;
    }

    public static function getConfig($file) {
        $__preInclude = get_defined_vars();
        include $file;
        $__postInclude = get_defined_vars();
        unset($__postInclude['__preInclude']);
        return array_diff_key($__postInclude, $__preInclude);
    }

    protected static function loadConfig($rootPath) {
        $yac = new \Yac();
        $ns  = md5($rootPath);

        $confKey   = "{$ns}-config";
        $mtimeKey  = "{$ns}-mtime";
        $cacheData = $yac->get(array($confKey, $mtimeKey));

        $confArr = !empty($cacheData[$confKey]) ? $cacheData[$confKey] : array();
        $mtimes  = !empty($cacheData[$mtimeKey]) ? $cacheData[$mtimeKey] : array();

        self::refreshConfigRecursive($rootPath, $confArr, $mtimes);

        $yac->set(array(
            $confKey  => $confArr,
            $mtimeKey => $mtimes,
        ));

        return $confArr;
    }

    protected static function refreshConfigRecursive($rootPath, &$confArr, &$mtimes = array()) {
        if (!is_dir($rootPath)) {
            return ;
        }

        $dp = opendir($rootPath);
        while ($fname = readdir($dp)) {
            $fpath = "{$rootPath}/{$fname}";
            if ($fname == '.' || $fname == '..') {
                continue;
            } else if (is_dir($fpath)) {
                $confArr[$fpath] = array();
                self::refreshConfigRecursive($fpath, $confArr[$fpath], $mtimes);
            } else if (is_file($fpath) && substr($fpath, - 4) === '.php') {
                $mtime   = filemtime($fpath);
                $confKey = preg_replace(';\.php$;i', '', $fname);
                if (!isset($mtimes[$fpath]) || $mtimes[$fpath] < $mtime) { // 第一次加载, 或者已经过期
                    $__preInclude = get_defined_vars();
                    include $fpath;
                    $__postInclude = get_defined_vars();
                    unset($__postInclude['__preInclude']);
                    $confArr[$confKey] = array_diff_key($__postInclude, $__preInclude);
                    $mtimes[$fpath] = $mtime;
                }
            }
        }
    }

}
