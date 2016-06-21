#  整体架构

##  整体架构图

![整体架构图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0001.ice-core-arch.png)

##  目录结构介绍

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
    Message/                    # 针对跨地域IDC的分布式通信方案(未生产环境验证)
    Frame/
        Abs/                    # 框架的一些基础抽象类
        Error/                  # 框架错误处理
        Runner/                 # 启动器. 共三类: web(建议做面向用户的应用入口), service(建议做内部服务), daemon(建议做后台任务处理)
        Daemon/                 # daemon启动器内部处理涉及的相关代码
        Service/                # service启动器内部处理涉及的相关代码. 服务管理proxy.
        Web/                    # web启动器内部处理涉及相关代码.
        Embeded/                # embeded启动器内部处理涉及相关代码
        Feature.php             # Feature组件
        Config.php              # 配置加载工具
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
    embeded_demo.php            # 嵌入式Runner的示例
    conf/
        app.php                 # 应用主配置
        service.inc             # service runner的个性化配置
        daemon.inc              # daemon runner的个性化配置
        web.inc                 # web runner的个性化配置
        service.php             # 依赖的服务配置文件
        resource.php            # 依赖的资源配置文件
        message.php             # 跨地域IDC的分布式通信消息配置(未生产环境验证)
```

##  核心类层次结构

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

