# 运行方式

## 运行方式的差异

故名思议, 运行方式决定了框架启动过程的差异性.

启动过程的差异性, 是为了解决不同的问题.

Ice中提供了四种不同的启动方式, 针对3种典型的Web开发应用场景, 以及一种特殊的兼容方式.

* 最常见的Web开发, 就是拿到输入数据, 处理输入数据, 操作后端存储(读/写), 将操作结果返回给用户.(html/json/xml等)

* 随着用户端应用场景的多样化(pc/h5/app/openapi), 通用逻辑的复用需要, 使得我们把公共的业务逻辑独立出来, 多方使用. 这样,  就促生了另外一个应用场景: 服务.

* 另外, 有些业务逻辑是耗时的, 有些业务是需要后台定时处理一些数据的. 对这类业务, 通常需要一些后台的任务去处理. 这些任务, 就形成了守护进程的应用场景.

* 第四种应用场景, 是为了帮助将已有业务系统平滑迁移到Ice而生的. 一个庞大的系统, 要迁移不会一蹴而就, 需要一个渐进的过程. Ice通过embeded运行方式, 将Ice框架加载进来, 让旧的系统, 可以非常简单的使用基于Ice实现的服务, 从而达到逐步迁移的目的.

## 运行方式的共同点

四种运行方式有其差异性场景, 但也存在共性的地方.

* Runner::run()方法, 执行这种运行方式的启动过程.

* 一次运行, 代表处理一个请求(对web而言, 是一个用户请求; 对service而言, 是一次服务调用; 对daemon而言, 是一个命令执行)

* 一次请求的数据交互, 通过Request/ClientEnv/ServerEnv/Response四个对象描述

    * Request: 代表请求参数.

    * ClientEnv: 代表客户端环境. 比如对客户端接口而言, 系统版本, 应用版本, 网络制式等信息.

    * ServerEnv: 代表服务端环境. 比如主机名.

    * Response: 代表响应.

* 无论哪种运行方式, 路由之后对应的处理逻辑, 均分为class/action两层. 路由结果需要设置到Request/Response对象中

## 基础配置

如下, 除了基础配置外, 每种运行方式, 对应一套运行时配置.

对于web方式, 因为作为入口, 通常会有更多的通用逻辑处理, 因此, 暴露了frame相关的配置项, 可以自定义交互数据对象.

另外, web方式涉及用户交互, 因此提供了更加复杂的路由支持, 以及模板引擎层的支持.

下面配置, 在应用中会被\F_Ice::$ins->runner->mainAppConf以数组方式持有, 其中的runner部分, 会替换为具体运行方式对应的配置.

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