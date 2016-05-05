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
        $logConfigs = $this->config->get('app.log');
        if (isset($logConfigs) && is_array($logConfigs)) {
            foreach ($logConfigs as $loggerName => $logConfig) {
                $loggerName = "logger_$loggerName";
                $this->$loggerName = new \F_Logger($logConfig);
            }
        }
    }

    protected function preSwitch() {
    }

    protected function postSwitch() {
    }

    public function __get($name) {
        // logger对象, 无配置则自主注册为桩对象
        if (strpos($name, 'logger_') === 0) {
            $this->$name = new \U_Stub();
            return $this->$name;
        }
    }
}
