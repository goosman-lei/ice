# Web运行方式

![Web应用结构图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0004.ice-core-func-runner-web.png)

## Action

Action作为Web运行方式的程序入口.

代码目录为: src/action/<class>/<action>.php

Action需要继承\FW_Action. 路由调度会自动执行Action的execute方法.

另外, Action可以覆写prevExecute和postExecute方法, 对应execute执行前后的HOOK.

## 路由

Web方式默认路由为静态路由. 即使用URL path部分的前两级路径解析.

同时, 在src/conf/web.inc中, 路由配置一项, 可以配置自定义路由规则, 或者自定义路由器.

自定义路由规则支持7种, 优先级均高于自定义路由器. 7种自定义路由的优先级

1. "==": 精确匹配
2. "i=": 不区分大小写精确匹配
3. "^=": 精确前缀匹配
4. "i^": 不区分大小写前缀匹配
5. "$=": 精确后缀匹配
6. "i$": 不区分大小写后缀匹配
7. "~=": 正则匹配
8. 自定义路由: 逗号分隔, 直到一个路由器返回TRUE

路由配置示例

```php
$web_routes = array(
    'default' => '\\Ice\\Frame\\Web\\Router\\RStatic',
    'i= /say/helloworld' => array(  # 不区分大小写的/say/helloworld访问, 会被路由到src/action/Say/Helloworld.php
        'class'  => 'Say',
        'action' => 'Helloworld',
    )
);
```

## 模板

Ice中没有提供自己的模板引擎. 但是对模板的适配进行了规范.

基于这套规范, 将模板分为两部分:

* 一部分是要使用的各种模板引擎

* 另一部分是哪些请求使用什么模板引擎的路由规则.

这样处理的原因在于, 很多项目初期, 是存在一个项目中多种输出数据的.(比如: Ajax接口要求Json返回, 普通接口要求HTML返回)

模板配置, 详见src/conf/web.inc

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
