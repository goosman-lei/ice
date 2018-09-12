<?php
namespace Ice\Frame;
class App {
    // static info
    public $rootPath;

    // resource
    public $config;
    public $proxy_resource;
    public $proxy_service;
    public $proxy_filter;

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
        self::$apps[$cachedSn]->_init();

        return self::$apps[$cachedSn];
    }

    public function getModel($name) {
        $className = '\\' . $this->config->get('app.namespace') . '\\Model\\' . ucfirst($name);
        if (class_exists($className)) {
            return new $className();
        } else {
            return new \U_Stub();
        }
    }

    public function __construct($rootPath, $runType) {
        $this->rootPath = $rootPath;
        $this->runType = $runType;

        $this->config = \F_Config::buildForApp($this);
    }
        
    public function _init() {
        $logConfigs = $this->config->get('app.runner.log');
        $loggerConfigs = $this->config->get('app.logger_config');
        if(isset($loggerConfigs) && is_array($loggerConfigs)){
            $logConfigs = array_merge($loggerConfigs, $logConfigs);
        }
        if (isset($logConfigs) && is_array($logConfigs)) {
            foreach ($logConfigs as $loggerName => $logConfig) {
                $loggerName = "logger_$loggerName";
                $this->$loggerName = new \F_Logger($logConfig);
            }
        }

        $this->proxy_resource = \Ice\Resource\Proxy::buildForApp($this);
        $this->proxy_service  = \Ice\Frame\Service\Proxy::buildForApp($this);
        $this->proxy_filter   = \Ice\Filter\Proxy::buildForApp($this);

        $this->init(); // 提供给应用层扩展使用
    }

    public function init() {
    }

    public function prevSwitch() {
    }

    public function postSwitch() {
    }

    public function __get($name) {
        // logger对象, 无配置则自主注册为桩对象
        if (strpos($name, 'logger_') === 0) {
            $this->$name = new \U_Stub();
            return $this->$name;
        }
    }
}
