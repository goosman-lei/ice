<?php
/*
 每个日志项, 会被自动注册到自己App的成员变量. 比如comm注册为$app->logger_comm
*/
$web_logger = array(
    'comm' => array(
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
        'log_path' => $var_path . '/logs',
        'split'    => array(
            'type' => 'file',
            'fmt'  => '-Ymd',
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
            'fmt'  => '-Ymd',
        ),
    ),
);

/*
1. "==": 精确匹配
2. "i=": 不区分大小写精确匹配
3. "^=": 精确前缀匹配
4. "i^": 不区分大小写前缀匹配
5. "$=": 精确后缀匹配
6. "i$": 不区分大小写后缀匹配
7. "~=": 正则匹配. 支持子组设置参数
8. 自定义路由: 逗号分隔, 直到一个路由器返回TRUE
demo: array(
    'default' => '\\Ice\\Frame\\Web\\Router\\RStatic',
    'i= /say/helloworld' => array(
        'class'  => 'Say',
        'action' => 'Helloworld',
    )
)
*/
$web_routes = array(
    'default' => '\\Ice\\Frame\\Web\\Router\\RStatic',
);

$web_temp_engine = array(
    'engines' => array(
        'json' => array(
            'adapter' => '\\Ice\\Frame\\Web\\TempEngine\\Json',
            'adapter_config' => array(
                'headers' => array('Content-Type: text/json;CHARSET=UTF-8'),
                'json_encode_options' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                'error_tpl' => '',
            ),
        ),
        'smarty-default' => array(
            'adapter' => '\\Ice\\Frame\\Web\\TempEngine\\Smarty',
            'adapter_config'  => array(
                'headers'   => array('Content-Type: text/html;CHARSET=UTF-8'),
                'error_tpl' => '_common/error',
                'ext_name'  => '.tpl',
            ),
            'temp_engine_config' => array(
                'cache_lifetime'        => 30 * 24 * 3600,
                'caching'               => false,
                'cache_dir'             => '',
                'use_sub_dirs'          => TRUE,
                'template_dir'          => $root_path . '/smarty-tpl',
                'plugins_dir'           => array(),
                'compile_dir'           => $run_path . '/smarty-compiled/' ,
                'default_modifiers'     => array('escape:"html"'),
                'left_delimiter'        => '{%',
                'right_delimiter'       => '%}',
            ),
        ),
    ),
    'routes' => array(
        '*' => 'json',
    ),
);

$web_frame = array(
    'server_env_class' => '\\Ice\\Frame\\Web\\ServerEnv',
    'client_env_class' => '\\Ice\\Frame\\Web\\ClientEnv',
    'request_class'    => '\\Ice\\Frame\\Web\\Request',
    'response_class'   => '\\Ice\\Frame\\Web\\Response',
    'base_uri'         => '',
    'default_class'    => 'index',
    'default_action'   => 'index',
);

$web_filter = array(
    'compile_path' => $run_path . '/filter',
    'base_filter'  => '\\Ice\\Filter\\Filter',
    'extend_path'  => $root_path . '/filter',
);
