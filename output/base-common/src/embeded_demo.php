<?php
require_once __DIR__ . '/../vendor/autoload.php';

$root_path = __DIR__;
$var_path  = $root_path . '/../var';
$options = array(
    'client_ip'  => '0.0.0.0', # 客户端IP地址
    'class'      => 'index',   # controller类, 无意义, 仅用作日志
    'action'     => 'index',   # action方法, 无意义, 仅用作日志
    'request_id' => '',        # 请求ID, 可选
);
$logger_config = array(
    'comm' => array(
        'log_fmt' => array(
            'fmt_time'            => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
        ),
        'log_fmt_wf' => array(
            'fmt_time'            => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
            'level'               => '',
            'errno'               => '',
            'trace'               => '',
        ),
        'log_file' => 'web.log',
        'log_path' => $var_path . '/logs',
        'split'    => array(
            'type' => 'file',
            'fmt'  => 'Ymd',
        ),
    ),
    'ws' => array(
        'log_fmt' => array(
            'fmt_time'            => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
        ),
        'log_fmt_wf' => array(
            'fmt_time'            => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
            'level'               => '',
            'errno'               => '',
            'trace'               => '',
        ),
        'log_file' => 'web.ws.log',
        'log_path' => $var_path . '/logs',
        'split'    => array(
            'type' => 'file',
            'fmt'  => 'Ymd',
        ),
    ),
);
$config = array(
    'root_path' => $root_path,
    'var_path'  => $root_path . '/../var',
    'run_path'  => $var_path . '/run',
    'log_path'  => $var_path . '/logs',
    'conf_path' => $root_path . '/conf',

    'runner' => array(
        'log' => $logger_config,
    ),
);
\Ice\Frame\Runner\Embeded::embed($options, $config);

$proxy = \F_Ice::$ins->mainApp->proxy_service->get('demo-remote', 'Say');
$data  = $proxy->hello('Jack');
var_dump($data);
