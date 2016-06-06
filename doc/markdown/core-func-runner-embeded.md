# Embeded运行方式

## Embeded

嵌入, 是一种特殊的应用场景.

它本身是为了将其他的PHP代码, 和Ice粘合起来. 使其可以方便的使用基于Ice开发的服务.

下面是一个嵌入的示例代码(tpl/src/embeded_demo.php)

```php
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

# 执行嵌入的方法调用, $options中描述当前请求环境, $config指定配置信息.
# 注意, 这里配置中会多一个conf_path. 这个conf_path的路径下, 按照Ice的配置规范包含资源和服务等配置文件即可.
\Ice\Frame\Runner\Embeded::embed($options, $config);

$proxy = \F_Ice::$ins->mainApp->proxy_service->get('demo-remote', 'Say');
$data  = $proxy->hello('Jack');
var_dump($data);
```
