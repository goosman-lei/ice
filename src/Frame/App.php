<?php
namespace Ice\Frame;
class App {
    // static info
    public $rootPath;

    // resource
    public $config;
    public $proxy_resource;
    public $proxy_service;

    public $runType;

    protected static $apps = array();

    public static function getServiceApp($projectGroup, $projectName) {
        $cachedSn = "/$projectGroup/$projectName";

        if (isset(self::$apps[$cachedSn])) {
            return self::$apps[$cachedSn];
        }

        $rootPath = \F_Ice::$ins->mainApp->rootPath . '/../vendor/' . $projectGroup . '/' . $projectName . '/src';
        $runType  = 'service';
        self::$apps[$cachedSn] = new self($rootPath, $runType);

        return self::$apps[$cachedSn];
    }

    public function __construct($rootPath, $runType) {
        $this->rootPath = $rootPath;
        $this->runType = $runType;

        $this->config = \F_Config::buildForApp($this);

        $this->init();
    }

    protected function init() {
        $logConfigs = $this->config->get('app.runner.log');
        if (isset($logConfigs) && is_array($logConfigs)) {
            foreach ($logConfigs as $loggerName => $logConfig) {
                $loggerName = "logger_$loggerName";
                $this->$loggerName = new \F_Logger($logConfig);
            }
        }

        $this->proxy_resource = \Ice\Resource\Proxy::buildForApp($this);
        $this->proxy_service  = \Ice\Frame\Service\Proxy::buildForApp($this);
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
