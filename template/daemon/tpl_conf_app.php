<?php
$namespace = '${PROJECT_NAMESPACE}';
$app_class = '\\Ice\\Frame\\App';

$root_path = __DIR__ . '/..';
$var_path  = $root_path . '/../var';
$run_path  = $var_path . '/run';
$log_path  = $var_path . '/logs';

/*
 每个日志项, 会被自动注册到自己App的成员变量. 比如common注册为$app->logger_common
*/
$log = array(
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
