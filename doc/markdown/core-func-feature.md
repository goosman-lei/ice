#  Feature组件

Feature组件是一个实现简单, 应用简单, 但在App的API开发中, 针对版本差异性开发, 非常有用的工具.

## Feature组件的设计思路

Feature组件的设计思路, 源自C语言预编译中的宏设计.

在C语言中, 很多时候, 需要基于一些宏的限定条件, 执行响应的逻辑

比如, 下面代码摘自PHP内核中php_request_startup()函数的实现:

```c
#ifdef HAVE_DTRACE
    DTRACE_REQUEST_STARTUP(SAFE_FILENAME(SG(request_info).path_translated), SAFE_FILENAME(SG(request_info).request_uri), (char *)SAFE_FILENAME(SG(request_info).request_method));
#endif /* HAVE_DTRACE */

#ifdef PHP_WIN32
    PG(com_initialized) = 0; 
#endif

#if PHP_SIGCHILD
    signal(SIGCHLD, sigchld_handler);
#endif

每一种宏, 代表了一种场景, 在符合场景要求时, 对应的代码才生效
```

Ice中的Feature就是借鉴这个思想而来的.

## Ice中的Feature应用

和C语言中的宏的使用非常相似.

```php
// 如果是ios 9以上版本, 差异性逻辑
if ($this->feature->isEnable('ge-ios-9')) {
    // ...
}

// 如果是baidu渠道3.2以上版本, 差异性逻辑
if ($this->feature->isEnable('baidu-ge-3.2')) {
    // ...
}
```

## Ice中Feature的配置

上面给出了Feature在应用逻辑编写时候的示例.

那ge-ios-9, baidu-ge-3.2这些名字(feature)是哪来的呢?

只需要在src/conf/feature.php中增加配置即可.

```php
<?php
$config = array(
    '*' => array(
        'ge-ios-9' => array(
            'osName eq ios',
            'osVersion v>= 9.0',
        ),
        'baidu-ge-3.2' => array(
            'channel eq baidu',
            'osName eq android',
            'appVersion v>= 3.2',
        ),
    ),
    '/say/hello' => array(),
);

Feature的配置, 是可以限定URI的(/controller/action), 限定URI, 则仅访问对应URI才会生效, 否则, 放在'*'下的, 对所有请求都生效.
```

Ok. 上面就是Feature的配置. 那配置中的osName, osVersion等是哪里来的呢?

Ice中将输入输出数据抽象为四种: ClientEnv, ServerEnv, Request, Response. 其中, ClientEnv就代表了客户端的环境.

而ClientEnv的实现是开放的, 你可以随时往ClientEnv上添加数据.

```php
\F_Ice::$ins->runner->clientEnv->osName = \F_Ice::$ins->runner->request->getQuery('osn');
\F_Ice::$ins->runner->clientEnv->osVersion = \F_Ice::$ins->runner->request->getQuery('osv');
...
```

在系统初始化阶段, 应用上统一的将客户端环境信息设置到ClientEnv中, 这样, 就拥有了一套基于客户端环境, 灵活的做差异性逻辑处理的Feature机制.
