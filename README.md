#  文档

* [Go to Manual Pages](http://goosman-lei.github.io/ice/index.html)

# 介绍

PHP-Web开发框架.

## 基于Ice的项目开发过程

![项目开发过程](http://static-cdn.tec-inf.com/post-img/0009.ice-core-development-progress.png)

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
