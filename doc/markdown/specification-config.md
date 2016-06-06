#  配置规范

##  src/conf/app.php

```php
$namespace = 'ice\demo';
$app_class = '\\Ice\\Frame\\App';

$root_path = __DIR__ . '/..';
$var_path  = $root_path . '/../var';
$run_path  = $var_path . '/run';
$log_path  = $var_path . '/logs';

// 引用三种runner对应的个性化配置
@include(__DIR__ . '/web.inc');
@include(__DIR__ . '/service.inc');
@include(__DIR__ . '/daemon.inc');

// 组装整体的运行时配置. 启动器会根据自己的启动器类型自动选择对应配置
$runner = array(
    'web' => array(
        'frame'       => $web_frame,
        'routes'      => $web_routes,
        'temp_engine' => $web_temp_engine,
        'log'         => $web_logger,
        'filter'      => $web_filter,
    ),  
    'service' => array(
        'log'    => $service_logger,
        'filter' => $service_filter,
    ),  
    'daemon' => array(
        'log'    => $daemon_logger,
        'filter' => $daemon_filter,
    ),  
);
```

##  src/conf/resource.php

```php
// scheme到自定义实现的映射. 用于扩展自己的资源处理器
$mapping = array(
    'connector' => array(
        'redis' => '\\ice\\demo\\Lib\\RedisConnector',
    ),
    'handler'   => array(
        'redis' => '\\ice\\demo\\Lib\\RedisHandler',
    ),
    'strategy'  => array(),
);

// 资源的获取语法为:
// $proxy->get(<scheme> "://" <unitname> [ "/" <cluster> ] [ "?force_new=true&algo=random" ]
// scheme定义了资源类型
// unitname定义了业务单元.
// cluster定义了不同的集群. 比如mysql的主从
// QueryString中定义了两个选项:
//      algo=random: 用来指定调度策略. 内部仅实现了random
//      force_new=true: 指定强制获取新的资源

// 资源的配置结构, 是一个多层次结构: scheme > unitname > cluster > node
// 最小单元就是节点(node). 下面例子中, 就是 array('host' => '127.0.0.1', 'port' => 3306)
//      (调度算法站额对的就是指定集群中, 多个节点的选择策略)
// 配置结构中的options是多级继承的, 每一层都可以有options. 下层会自动继承merge上层的options
$pool = array(
    'mysqli' => array(
        'options' => array(
            'deny_empty_update_delete' => TRUE,
            'warn_sql_length' => 51200,
            'fatal_sql_length' => 2097152,
        ),
        'demo' => array(
            'master' => array(
                array('host' => '127.0.0.1', 'port' => 3306),
            ),
            'slave' => array(
                array('host' => '127.0.0.1', 'port' => 3306),
            ),
            'options' => array(
                'timeout' => 1,
                'user'    => '',
                'passwd'  => '',
            ),
        ),
    ),
    'curl' => array(
        'service' => array(
            'default' => array(
                array('host' => 'service.host.com') // 修改为service的host
            ),
        ),
    ),
);
```

##  src/conf/service.php

```php
$pool = array(
    'demo-local' => array(
        'proxy'  => 'local',
        'config' => array(
            'project_group' => 'ice',
            'project_name'  => 'demo_service',
        ),
    ),
    'demo-remote' => array(
        'proxy'  => 'remote',
        'config' => array(
            'resource' => 'curl://service',
        ),
    ),
);

Service调用有三种方式(下面代码是嘉定在action中调用/say/hi服务):

$this->ice->mainApp->proxy_service->get('internal', 'Say')->hi();
$this->ice->mainApp->proxy_service->get('demo-local', 'Say')->hi();
$this->ice->mainApp->proxy_service->get('demo-remote', 'Say')->hi();


其中:
internal是保留字, 表示调用当前app内部的service.

另外两种方式(demo-local, demo-remote), 分别对应了src/conf/service.php中的两个配置

demo-local:
    'demo-local' => array(
        'proxy'  => 'local',
        'config' => array(
            'project_group' => 'ice',
            'project_name'  => 'demo_service',
        ),
    ),
proxy = local 用来说明此项目是本地部署(composer依赖)
config中指明了项目的GROUP和NAME, 框架会自动对应到vender/ice/demo_service去加载服务

demo-remote:
    'demo-remote' => array(
        'proxy'  => 'remote',
        'config' => array(
            'resource' => 'curl://service',
        ),
    ),
proxy = remote 用来说明此服务是远程部署, 通过HTTP WebService调用方式使用服务
$config中指明了服务对应的资源(参考src/conf/resource.php). 框架会自动使用资源管理器, 获取对应资源并请求服务.
```

##  web应用对应的模板引擎配置(src/conf/web.inc)

```php
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
        'say' => array(
            '*' => 'smarty-default',
            'hi' => 'json',
        ),
    ),
);

'engines'部分, 配置支持的模板引擎.
上面例子中, 配置了json和smarty-default两种模板引擎.
我们认为, 一套具体配置出来的模板引擎实例, 是应用选择模板引擎的最小单元.
(同样使用smarty作为引擎, 但不同的模块可能需要不同的渲染选项)

'routes'部分, 描述了一个请求, 怎样去选择应用那个模板引擎.
以上面配置为例:
/say/hi   这个请求, 使用json渲染
/say/*    除了/say/hi外的所有/say/*请求, 均使用smarty-default渲染
*         其他所有请求, 均使用json渲染

整个routes部分的配置, 面向controller/action两层结构.
'*'表示某层的默认引擎
```

