<?php
/*
 每个日志项, 会被自动注册到自己App的成员变量. 比如comm注册为$app->logger_comm
*/
$app_logger = array(
    'api' => array(
        'log_fmt' => array(
            'fmt_time'            => '', # 默认-Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
        ),
        'log_fmt_wf' => array(
            'fmt_time'            => '', # 默认-Y-m-d H:i:s
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
        'log_file' => 'service.log',
        'log_path' => $var_path . '/logs/servicelog',
        'split'    => array(
            'type' => 'file',
            'fmt'  => '-Ymd',
        ),
    ),
    'webapi' => array(
        'log_fmt' => array(
            'fmt_time'            => '', # 默认-Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.uri'         => '',
            'request.originalUri' => '',
            'request.class'       => '',
            'request.action'      => '',
            'request.id'          => '',
        ),
        'log_fmt_wf' => array(
            'fmt_time'            => '', # 默认-Y-m-d H:i:s
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
        'log_path' => $var_path . '/logs/weblog',
        'split'    => array(
            'type' => 'file',
            'fmt'  => '-Ymd',
        ),
    ),
);
