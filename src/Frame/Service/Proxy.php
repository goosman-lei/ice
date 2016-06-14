<?php
namespace Ice\Frame\Service;
class Proxy {

    protected $_pool = array();
    protected $_confArr = array();

    protected function __construct() {
    }

    public static function buildForApp($app) {
        $proxy = new self();

        $proxy->_pool = $app->config->get('service.pool');
        $proxy->initConf();

        return $proxy;
    }

    protected function initConf() {
        if (empty($this->_pool)) {
            return ;
        }

        foreach ($this->_pool as $serviceName => $serviceInfo) {
            $proxyClass = '\\Ice\\Frame\\Service\\Proxy\\' . ucfirst(strtolower($serviceInfo['proxy']));
            if (!class_exists($proxyClass)) {
                \F_Ice::$ins->mainApp->logger_ws->warn(array(
                    'service_name' => $serviceName,
                    'service_info' => $serviceInfo,
                    'proxy_class'  => $proxyClass,
                ), \F_ECode::WS_PROXY_UNKONW_PROXY);
            }

            $this->_confArr[$serviceName] = array(
                'proxy_class'    => $proxyClass,
                'service_config' => $serviceInfo['config'],
            );
        }
    }

    public function get($serviceName, $class = null) {
        if ($serviceName == 'internal') {
            $serviceInfo = array(
                'proxy_class' => '\\Ice\\Frame\\Service\\Proxy\\Internal',
                'service_config' => array(),
            );
        } else if ($serviceName == 'message') {
            $serviceInfo = array(
                'proxy_class' => '\\Ice\\Frame\\Service\\Proxy\\Message',
                'service_config' => array(),
            );
        } else if (!isset($this->_confArr[$serviceName])) {
            \F_Ice::$ins->mainApp->logger_ws->warn(array(
                'service_name' => $serviceName,
            ), \F_ECode::WS_PROXY_UNKONW_SERVICE);
            return new \U_Stub();
        } else {
            $serviceInfo = $this->_confArr[$serviceName];
        }

        $proxyClass    = $serviceInfo['proxy_class'];
        $serviceConfig = $serviceInfo['service_config'];

        return new $proxyClass($serviceConfig, $class);
    }
}