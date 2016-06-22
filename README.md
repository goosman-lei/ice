#  文档TOC

* [整体架构](http://goosman-lei.github.io/ice/arch.html)
* 核心功能
    * [运行方式](http://goosman-lei.github.io/ice/core-func-runner.html)
        * [Web](http://goosman-lei.github.io/ice/core-func-runner-web.html)
        * [Service](http://goosman-lei.github.io/ice/core-func-runner-service.html)
        * [Daemon](http://goosman-lei.github.io/ice/core-func-runner-daemon.html)
        * [Embeded](http://goosman-lei.github.io/ice/core-func-runner-embeded.html)
    * [Filter](http://goosman-lei.github.io/ice/core-func-filter.html)
    * [资源管理](http://goosman-lei.github.io/ice/core-func-resource.html)
    * [输入输出](http://goosman-lei.github.io/ice/core-func-input-output.html)
    * [DB查询工具](http://goosman-lei.github.io/ice/core-func-db.html)
    * [Feature机制](http://goosman-lei.github.io/ice/core-func-feature.html)
    * [日志处理](http://goosman-lei.github.io/ice/core-func-logger.html)
* [命名规范](http://goosman-lei.github.io/ice/specification-name.html)
* [开发规范](http://goosman-lei.github.io/ice/specification-develop.html)

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

* 一套客户端差异性Feature管理机制: $ice->runner->feature->isEnable('ios-ge-7')
	
* 一组框架基础工具集: Logger, Config
	
##  外围工具

* 一套基础工具库: Util. 期望将通用数据结构, 常用数据操作的算法等, 在Util中沉淀积累.
	
* 一套基于mysqli的数据库工具集: DB_Query. 提供过程化的SQL查询方法, 简化DB操作.
	
* 一套数据过滤工具集: $app->proxy_filter.
