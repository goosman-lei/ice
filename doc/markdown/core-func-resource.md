# 资源管理

![资源管理模块设计图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0007.ice-core-func-resource.png)

## 设计思路

设计思路, 主要基于下面的思考

* 任何面向连接的资源访问, 均可抽象两个步骤组成: 连接(含授权), 命令.(忽略关闭连接, 语言层面已经解决)

* 对资源的管理, 比如熔断, 降级等, 是通用策略, 并且通常是应用在连接层面. (某些场景命令层面可提供数据)

* 基于此, Ice将一个资源拆分为Connector和Handler两部分. 并在外层增加一个proxy调度层, 用作对资源的调度管理.

Ice中的资源管理, 其价值主要在于分离了资源获取和资源使用两块, 在资源获取阶段, 通过调度层, 可以方便的进行调度管理.

## 配置详解

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

## 应用示例

```php
$facebookCurl = \F_Ice::$ins->workApp->proxy_resource->get('curl://facebook-page');
$wechatCurl   = \F_Ice::$ins->workApp->proxy_resource->get('curl://wechat');
$userMysql    = \F_Ice::$ins->workApp->proxy_resource->get('mysqli://user/master');

$facebookCurl->get('/');
$wechatCurl->get('/');
$userMysql->query('SHOW TABLES');
```
