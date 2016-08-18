<?php
namespace Ice\Frame;
class Config {

    protected $confArr;

    public function __construct($rootPath, $globalConfPath = null) {
        $this->confArr = self::loadConfig($rootPath, $globalConfPath);
    }

    public static function buildForApp($app) {
        if ($app->runType === 'embeded') {
            $config = new self(@\F_Ice::$ins->runner->mainAppConf['conf_path'], @\F_Ice::$ins->runner->mainAppConf['conf_global_path']);
            $config->confArr['app'] = \F_Ice::$ins->runner->mainAppConf;
        } else {
            $config = new self($app->rootPath . '/conf', $app->rootPath . '/conf_global');
            $config->confArr['app']['runner'] = $config->confArr['app']['runner'][$app->runType];
        }
        return $config;
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

    protected static function loadConfig($rootPath, $globalConfPath = null) {
        $globalConfArr = array();
        if($globalConfPath){
            self::refreshConfigRecursive($globalConfPath, $globalConfArr);
        }
        
        $confArr = array();
        self::refreshConfigRecursive($rootPath, $confArr);
        return array_merge($globalConfArr, $confArr);
    }

    protected static function refreshConfigRecursive($rootPath, &$confArr) {
        if (!is_dir($rootPath)) {
            return ;
        }

        $dp = opendir($rootPath);
        while ($fname = readdir($dp)) {
            $fpath = "{$rootPath}/{$fname}";
            if ($fname == '.' || $fname == '..') {
                continue;
            } else if (is_dir($fpath)) {
                $confArr[$fname] = array();
                self::refreshConfigRecursive($fpath, $confArr[$fname]);
            } else if (is_file($fpath) && substr($fpath, - 4) === '.php') {
                $confKey = preg_replace(';\.php$;i', '', $fname);

                $confArr[$confKey] = self::getConfig($fpath);
            }
        }
    }

}
