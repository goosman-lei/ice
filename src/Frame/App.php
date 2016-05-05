<?php
namespace Ice\Frame;
class App {
    // static info
    public $rootPath;

    // resource
    public $config;

    protected static $apps = array();

    public static function getApp($appName) {
        return self::$apps[$appName];
    }

    public static function registerApp($appName, $app) {
        self::$apps[$appName] = $app;
    }

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
        $this->config = new \F_Config($this->rootPath . '/conf');

        $this->init();
    }

    protected function init() {
        if (isset($logConfigs) && is_array($logConfigs)) {
            foreach ($logConfigs as $logName => $logConfig) {
                $this->$logName = new \Ice\Frame\Logger($logConfig);
            }
        }
    }

    protected function preSwitch() {
    }

    protected function postSwitch() {
    }

    public function __get($name) {
        // logger对象, 无配置则自主注册为桩对象
        if (strpos($name, 'logger_')) {
            $this->$name = new \U_Stub();
        }
    }
}
