<?php
/*
 每个日志项, 会被自动注册到自己App的成员变量. 比如common注册为$app->logger_common
*/
$daemon_logger = array(
    'common' => array(
        'log_fmt' => array(
            'fmt_now'             => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.class'       => '',
            'request.action'      => '',
        ),
        'log_fmt_wf' => array(
            'fmt_now'             => '', # 默认Y-m-d H:i:s
            'client_env.ip'       => '',
            'server_env.hostname' => '',
            'request.class'       => '',
            'request.action'      => '',
            'level'               => '',
            'errno'               => '',
            'trace'               => '',
        ),
        'log_file' => 'daemon.log',
        'log_path' => $var_path . '/logs',
        'split'    => array(
            'type' => 'file',
            'fmt'  => 'Ymd',
        ),
    ),
);
