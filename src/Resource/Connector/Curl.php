<?php
namespace Ice\Resource\Connector;
class Curl extends Abs {

    public static function mergeDefault($nodeConfig, $nodeOptions) {
        if (!isset($nodeOptions['scheme'])) {
            $nodeOptions['scheme'] = 'http';
        }
        if (!isset($nodeConfig['port'])) {
            $nodeConfig['port'] = getservbyname($nodeOptions['scheme'], 'tcp');
        }
        return array($nodeConfig, $nodeOptions);
    }

    public static function getSn($nodeConfig, $nodeOptions) {
        return sprintf('%s:%s:%s', $nodeOptions['scheme'], $nodeConfig['host'], $nodeConfig['port']);
    }

    public static function getConn($nodeInfo) {
        $curl  = curl_init();

        $options = $nodeInfo['options'];
        $config  = $nodeInfo['config'];

        $defaultOptions = array(
            CURLOPT_RETURNTRANSFER      => TRUE,  // 返回结果
            CURLOPT_FOLLOWLOCATION      => TRUE,  // 支持302跳转
            CURLOPT_MAXREDIRS           => 5,     // 最大跳转次数
            CURLOPT_NOSIGNAL            => TRUE,  // Hack不能设置1000ms内超时问题
            CURLOPT_CONNECTTIMEOUT_MS   => 100,   // 100ms连接超时默认
            CURLOPT_TIMEOUT_MS          => 500,   // 500ms读写超时默认
        );

        if (isset($options['setopt'])) {
            $usedOptions = $defaultOptions;
            if (!empty($options['setopt'])) {
                foreach ($options['setopt'] as $k => $v) {
                    $usedOptions[$k] = $v;
                }
            }
        } else {
            $usedOptions = $defaultOptions;
        }

        curl_setopt_array($curl, $usedOptions);

        return $curl;
    }
}
