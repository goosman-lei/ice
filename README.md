# ice

PHP-Web开发框架

##基础功能开发

* ✓ 三种运行方式: web, service, daemon
	
* ✓ 四种请求数据结构: Request, Response, ClientEnv, ServerEnv
	
* ✓ 两个框架基础封装: Ice, App
	
* ✓ 一个资源管理机制: $app->proxy_resource
	
* ✓ 一个服务管理机制: $app->proxy_service
	
* ✓ 一组框架基础工具集: Logger, Config
	
##外围工具开发

* ✓ 一套基础工具库: Util
	
* ✓ 一套基于mysqli的数据库工具集: DB_Query
	
* ✓ 一套数据过滤工具集: $app->proxy_filter
	
##协议规范

* 一套分层规范
	
* 一套静态代码检查工具
	
* 一份编码规范文档
	
* 一份架构介绍文档
	
* 一套Example使用示例

##整体架构

![整体架构图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0001.ice-core-arch.png)

###文件结构介绍

```php
框架文件结构
composer.json
class_alias.php                 # 类名映射. 在composer.json中已定义自动加载
tpl/                            # 用户生成演示应用的模板文件结构
bin/
    ice-skel                    # 用于生成ice的演示应用的脚本工具. (执行前根据需求修改tpl/build.conf)
src/
    DB/                         # DB访问基础封装. 结构化的方法调用来做SQL查询. Model层可继承DB_Query
    Filter/                     # 过滤器. 按照过滤器指定语法, 描述数据规则, 进行校验/数据修正
    Frame/
        Abs/                    # 框架的一些基础抽象类
        Error/                  # 框架错误处理
        Runner/                 # 启动器. 共三类: web(建议做面向用户的应用入口), service(建议做内部服务), daemon(建议做后台任务处理)
        Daemon/                 # daemon启动器内部处理涉及的相关代码
        Service/                # service启动器内部处理涉及的相关代码. 服务管理proxy.
        Web/                    # web启动器内部处理涉及相关代码.
        Ice.php                 # 框架主对象
        App.php                 # 应用对象.
        Logger.php              # 日志工具
    Resource/                   # 资源管理的封装
        Proxy.php               # 资源管理的入口proxy
        Connector/              # 资源的连接封装
        Handler/                # 资源的操作封装
        Strategy/               # 资源调度策略
    Util/                       # 基础数据结构, 通用工具的封装

(项目)应用内文件结构
composer.json
class_alias.php                 # 如有需要可自己在composer.json注册, 默认不使用
var/                            # 默认的运行时输出目录
    logs/                       # 默认日志路径
    run/                        # 运行时目录
        filter/                 # 默认的FILTER编译产出路径
src/
    action/                     # web入口层
    daemon/                     # daemon入口层
    service/                    # 服务层
    model/                      # Model层
    lib/                        # 应用的本地类库
    conf/
        app.php                 # 应用主配置
        service.inc             # service runner的个性化配置
        daemon.inc              # daemon runner的个性化配置
        web.inc                 # web runner的个性化配置
        service.php             # 依赖的服务配置文件
        resource.php            # 依赖的资源配置文件
```

###命名规范

* 所有类均遵循PSR4命名规范

* PROJECT_GROUP和PROJECT_NAME共同作用, 唯一定义一个项目
```
PROJECT_GROUP="ice"
PROJECT_NAME="demo"
```

* 项目均需定义在唯一的名字空间下, 建议直接使用PROJECT_GROUP和PROJECT_NAME组合构成顶层的两级名字空间
```
PROJECT_NAMESPACE="$PROJECT_GROUP\\$PROJECT_NAME"
```

* web应用的入口, 代码位于src/action. 内部保持两层结构(src/action/Controller/Action.php).
```
// 假定使用默认路由, 则URI /say/hi被解析到
src/action/Say/Hi.php

实现代码如下
<?php
namespace PROJECT_GROUP\PROJECT_NAME\Action\Say;
class Hi extends \FW_Action{
    public function execute() {
        return $this->success(array(
            'output' => 'Hi.'
        ));
    }
}
```
* daemon应用的入口, 代码位于src/daemon. 内部保持两层结构(src/action/Controller/Action.php)
```
调用命令:
    php src/daemon/cli.php --class=say --action=hi
将被解析到
src/daemon/Say/Hi.php

实现代码如下
<?php
namespace PROJECT_GROUP\PROJECT_NAME\Daemon\Say;
class Hi extends \FD_Daemon {
    public function execute() {
        return $this->output('Hi.');
    }
}
```

* service应用的入口, 代码位于src/service, 内部保持一层结构(src/action/Service.php)
```
对于服务say/hi
代码路径:
src/service/Say.php

示例实现代码如下
<?php
namespace PROJECT_GROUP\PROJECT_NAME\Service;
class Say extends \FS_Service {
    public function hi() {
        return 'Hi.';
    }
}
```

* 本地类库, 代码位于src/lib, 命名空间为PROJECT_GROUP\PROJECT_NAME\Lib
```
假定有本地类库Ip
则代码路径:
src/lib/Ip.php

示例实现代码如下:
<?php
namespace PROJECT_GROUP\PROJECT_NAME\Lib;
class Ip {
}
```

###核心类层次结构

![核心类层次结构](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0002.ice-core-class-hiberarchy.png)

* 三种启动方式的入口, 均持有$ice句柄

* 除Service外, 另外两种入口均持有四种输入输出数据对象(因为Service可跨应用使用, 因此仅关注自身协议, 不关注请求数据)

```
class Action {
    public $ice;

    public $request;
    public $response;
    public $clientEnv;
    public $serverEnv;
}
class Daemon {
    public $ice;

    public $request;
    public $response;
    public $clientEnv;
    public $serverEnv;
}
class Service {
    public $ice;
}
```

* 框架核心对象以$ice为入口展开

```
class Ice {
    public $mainApp;            // 主App(入口app)
    public $workApp;            // service的local调用, 会自动切换workApp

    public $runner;

    public static $ins;         // 框架主对象唯一实例
}

class Runner {
    public $request;
    public $response;
    public $clientEnv;
    public $serverEnv;

    public $ice;
}

class App {
    public $config;             // 当前应用的配置管理对象

    public $proxy_filter;       // 过滤器入口
    public $proxy_resource;     // 资源管理入口
    public $proxy_service;      // 服务管理入口

    public $logger_xxx;         // 自动注册的日志句柄
}
```

##Exapmle

###应用代码构建方法
```
1. 修改tpl/build.conf
2. 执行bin/ice-skel
3. 在tpl/build.conf指定的输出路径下, 就自动产生的应用代码

4. 将生成的应用代码, 建立新的git. 形成自己的项目

5. 部署:
    1) service: 所有请求从web server打到src/webroot/service.php
    2) web: 所有请求从web server打到src/webroot/web.php
```

###标准应用配置详解

####src/conf/app.php
```php
<?php
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

####src/conf/resource.php
```php
<?php
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

####src/conf/service.php
```php
<?php
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

####web应用对应的模板引擎配置(src/conf/web.inc)
```php
<?php
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

###应用开发中的Tips

* 请不要产生跨层调用, 正确的调用层次关系是
```
action  => service
daemon  => service
service => model

action  => lib
daemon  => lib
service => lib
model   => lib


$this->request->get
```

* 仅daemon/action下可以使用四种输入输出数据对象

* daemon的输入输出数据对象接口
```
$this->clientEnv 无可用信息

$this->serverEnv->hostname;
$this->serverEnv->argc; // 所有$_SERVER内变量均以映射到此对象

$this->request->options['option_name']; // 所有--long-opt=xxx -antp -n=1等标准UNIX命令行选项均解析到此数组
$this->request->argv[0];                // 所有除选项外的命令行参数, 均解析到此数组
$this->request->stdin;                  // 标准输入文件资源
$this->request->originalArgv;           // 原始命令行参数列表
$this->request->id;                     // 请求ID
$this->request->getOption($name, $default = null);
$this->request->hadOption($name);
$this->request->class;                  // 路由后的controller
$this->request->action;                 // 路由后的action

$this->response->stdout;                // 标准输出文件资源
$this->response->stderr;                // 标准错误文件资源
$this->response->class;                 // 路由后的controller
$this->response->action;                // 路由后的action

```

* web action的输入输出数据对象接口
```
$this->clientEnv->ip                    // 客户端IP地址

$this->serverEnv->hostname;
$this->serverEnv->argc; // 所有$_SERVER内变量均以映射到此对象

$this->request->getParams();            // 自定义路由设置的参数
$this->request->getQueries();           // 获取所有GET参数
$this->request->getPosts();             // 获取所有POST参数
$this->request->getCookies();           // 获取所有COOKIE
$this->request->getFiles();             // 获取所有上传文件结构
$this->request->getParam($name, $default = null);   // 获取单个自定义路由设置的参数
$this->request->getQuery($name, $default = null);   // 获取单个GET参数
$this->request->getPost($name, $default = null);    // 获取单个POST参数
$this->request->getCookie($name, $default = null);  // 获取单个Cookie
$this->request->getFile($name, $default = null);    // 获取单个上传文件
$this->request->getBody();              // 获取请求BODY
$this->request->id;                     // 请求ID
$this->request->uri;                    // 经过优化处理之后的URI
$this->request->originalUri;            // 原始请求URI
$this->request->class;                  // 路由后的controller
$this->request->action;                 // 路由后的action

$this->response->output();                         // 执行正常流的模板引擎输出逻辑
$this->response->error($errno, $data = array());   // 执行异常流的模板引擎输出逻辑
$this->response->appendBody($string);   // 向输出的body中添加原始字符串
$this->response->setBody($string);      // 设置输出body
$this->response->addHeader($header);    // 添加响应HEADER
$this->response->addCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = FALSE, $httponly = FALSE); // 添加响应时应用的Cookie
$this->response->class;                 // 路由后的controller
$this->response->action;                // 路由后的action
```
