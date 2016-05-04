<?php
namespace Ice\Frame;
class App {
    // static info
    public $rootPath;

    // resource
    public $config;

    // handler
    public $logger;

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
        if (!isset($this->logger_common)) {
            $this->logger_common = new \U_Stub();
        }
    }

    protected function preSwitch() {
    }

    protected function postSwitch() {
    }
}
