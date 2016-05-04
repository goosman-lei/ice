<?php
$namespace = 'demo\\ui';
$app_class = '\\Ice\\Frame\\App';
$base_uri = '';
$default_controller = 'index';
$default_action = 'index';

$root_path = __DIR__ . '/..';
$var_path  = $root_path . '/var';
$run_path  = $var_path . '/run';
$log_path  = $var_path . '/logs';

$frame = array(
    'server_env_class' => '\\Ice\\Frame\\Web\\ServerEnv',
    'client_env_class' => '\\Ice\\Frame\\Web\\ClientEnv',
    'request_class'    => '\\Ice\\Frame\\Web\\Request',
    'response_class'   => '\\Ice\\Frame\\Web\\Response',
);

$log = array(
    'common' => array( // common日志是固定的, 框架层会直接继承使用此logger
        'log_fmt' => array(),
        'log_fmt_wf' => array(),
        'log_file' => 'common.log',
        'log_path' => $var_path . '/logs',
        'split'    => array(
            'type' => 'file',
            'fmt'  => 'Ymd',
        ),
    ),
);
