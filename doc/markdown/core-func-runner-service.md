# Service运行方式

![Service应用结构图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0006.ice-core-func-runner-service.png)

## Service

Service作为service运行方式的程序入口.

代码目录为: src/service/<class>.php

Service需要继承\FS_Service. 路由调度会自动执行<class>的<action>方法.

## 设计思路

Ice看中服务的逻辑解耦, 部署方面认为是可接受混部的.

理由: 逻辑解耦的服务, 在资源不足或可用性考虑, 需要部署隔离时, 切换是低成本的.

在这个基础之上, 使用Ice开发的Service, 就期望可以做到以下两点:

* 本地调用逻辑解耦的服务, 需要隔离的上下文.

* 远程调用和本地调用切换对应用透明

## 实现方式

##### 第一点, 隔离的上下文, 得益于php的名字空间.

* 规范层面, 强制要求基于Ice开发的每个项目, 有一个唯一的名字<PROJECT_GROUP>/<PROJECT_NAME>来表示, 以此保证没有名字冲突.

* 机制层面, 每个项目, 在Ice中运行时, 都有一个App对象表示它, 这个对象持有这个项目相关的资源, 一切项目特定的操作/配置获取等, 都通过这个App对象展开.

通过上面两层的限定, Ice中的每个项目, 就有了独立的上下文环境.

多个项目同时运行时, 启动时的主App是有特殊含义的. 比如: 日志记录方式可能需要由主应用来决定.

因此, 在\F_Ice::$ins框架主对象中, 提供了两个App对象的引用

* \F_Ice::$ins->mainApp: 代表入口的主项目

* \F_Ice::$ins->workApp: 代表当前运行的项目

##### 第二点, 远程调用和本地调用的透明切换, 是通过一个proxy层来屏蔽细节的.

业务上需要使用其他服务时, 都是通过proxy_service的get方法, 得到服务的代理对象.

get()方法的第一个参数, 对应src/conf/service.php中的配置, 第二个参数, 指代服务的class名.

下面是服务配置及应用的示例代码

配置文件路径: src/conf/service.php

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
