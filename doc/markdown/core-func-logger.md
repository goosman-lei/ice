#  日志组件

日志是一个系统必不可少的部分.

## 日志和系统关系的思考

日志的记录时机, 主要取决于编码时的业务设计.

但是, 日志的记录方式(要不要记录日志, 记录在哪里, 什么格式), 则和运行时有很高的相关性.

(比如, Nginx可以在配置中, 去自定义日志的位置和记录的字段)

Ice中提供了4种运行方式(Runner), 日志的配置, 位于src/conf/app.php中, runner的部分.

不同的运行方式, 可以设置不同的日志规则.

## Ice中的记录日志的方式(对象归属关系)

在Ice框架中, \F_Ice::$ins是整个框架的核心对象. 它通过mainApp和workApp两个成员, 产生了和应用描述对象App的关系.

日志, 是应用相关的, 它作为App对象的logger_xxx成员, 供应用开发者调用.

例如, 如下配置:

```php
$runner = array(
    'web' => array(
        'log' => array(
            'comm' => array(...),
            'ws'   => array(...),
        )
    ),
);

则当应用以web方式运行时, 对应$app对象会自动注册成员

$app->logger_comm
$app->logger_ws
```

另外一点, 关于mainApp和workApp的差异.

mainApp就是指入口应用. workApp在应用启动时, 和mainApp相同, 只有当发生local类型的service调用时, workApp才会切换到被调用Service的App.


## Ice中的日志配置

```php
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

各配置项含义为:
log_fmt:    info级别的日志格式
log_fmt_wf: warning和fatal级别的日志格式(warning/fatal和正常的info日志分开, 是为了报警方便)
log_file:   日志文件名.
log_path:   日志文件存放路径
split:      日志切分规则
split.type: 日志切分方式. 支持file(文件名增加时间区分), dir(目录名增加时间区分), none(不切分)
split.fmt:  日志切分使用的时间格式串. 可用来控制切分粒度

log_fmt和log_fmt_wf中支持的子项有:
fmt_time:       格式化的时间戳(请求时间), 默认Y-m-m H:i:s, 可通过参数指定格式
fmt_now:        格式化的时间戳(记录日志当时时间), 默认Y-m-m H:i:s, 可通过参数指定格式
level:          日志级别(info, warn, fatal)
trace:          堆栈回溯信息
mem_used:       内存使用
client_env.xxx  当前ClientEnv对象的xxx属性
server_env.xxx  当前ServerEnv对象的xxx属性
request.xxx     当前Request对象的xxx属性
a.b.c           自定义日志项. 值为应用通过$logger->set('a.b.c', 'value')设置

注: 日志均使用json处理, 不使用常规的行式日志
```
