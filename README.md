#  文档TOC

* [整体架构](https://github.com/goosman-lei/ice/blob/master/doc/markdown/arch.md)
* 核心功能
    * [运行方式](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-runner.md)
        * [Web](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-runner-web.md)
        * [Service](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-runner-service.md)
        * [Daemon](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-runner-daemon.md)
        * [Embeded](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-runner-embeded.md)
    * [Filter](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-filter.md)
    * [资源管理](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-resource.md)
    * [输入输出](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-resource.md)
    * [DB查询工具](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-resource.md)
    * [Feature机制](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-feature.md)
    * [日志处理](https://github.com/goosman-lei/ice/blob/master/doc/markdown/core-func-logger.md)
* [命名规范](https://github.com/goosman-lei/ice/blob/master/doc/markdown/specification-name.md)
* [开发规范](https://github.com/goosman-lei/ice/blob/master/doc/markdown/specification-develop.md)
* [配置规范](https://github.com/goosman-lei/ice/blob/master/doc/markdown/specification-config.md)
* [API](https://github.com/goosman-lei/ice/blob/master/doc/markdown/api.md)

# 介绍

PHP-Web开发框架.

##  应用示例

```
1. 修改tpl/build.conf
2. 执行bin/ice-skel
3. 在tpl/build.conf指定的输出路径下, 就自动产生的应用代码

4. 将生成的应用代码, 建立新的git. 形成自己的项目

5. 部署:
    1) service: 所有请求从web server打到src/webroot/service.php
    2) web: 所有请求从web server打到src/webroot/web.php
```

##  核心功能

* 四种运行方式
	
* 四种交互数据结构
	
* 两个框架基础封装
	
* 一个资源管理机制: $app->proxy_resource
	
* 一个服务管理机制: $app->proxy_service
	
* 一组框架基础工具集: Logger, Config
	
##  外围工具

* 一套基础工具库: Util. 期望将通用数据结构, 常用数据操作的算法等, 在Util中沉淀积累.
	
* 一套基于mysqli的数据库工具集: DB_Query. 提供过程化的SQL查询方法, 简化DB操作.
	
* 一套数据过滤工具集: $app->proxy_filter.
