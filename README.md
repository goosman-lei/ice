#  文档TOC

* [整体架构](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/arch.md)
* 核心功能
    * [运行方式](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-runner.md)
        * [Web](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-runner-web.md)
        * [Service](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-runner-service.md)
        * [Daemon](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-runner-daemon.md)
        * [Embeded](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-runner-embeded.md)
    * [Filter](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-filter.md)
    * [资源管理](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-resource.md)
    * [输入输出](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-resource.md)
    * [DB查询工具](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/core-func-resource.md)
* [命名规范](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/specification-name.md)
* [开发规范](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/specification-develop.md)
* [配置规范](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/specification-config.md)
* [API](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/markdown/api.md)

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

* 四种运行方式:
    * web: 面向用户的服务入口. 包括PC/App-API/OpenAPI等场景.
    * service: 内部的服务层构建.
    * daemon: 内部的后台脚本服务.
    * embeded: 嵌入式接口, 将Ice嵌入到其他框架的应用场景. 典型应用场景: 旧框架切换到ice, 先用ice开发独立服务(逻辑解耦), 然后用嵌入式运行方式, 融合两者(逐步替换), 最终再达到分离的目的.
	
* 四种交互数据结构: 类比linux命令, 可以通过参数和环境变量两种方式与外界交互. 对Web环境而言, 多一个客户端环境, 因此, 将交互数据抽象为4类
    * Request: 输入的参数.
    * Response: 输出的数据.
    * ClientEnv: 客户端的环境.
    * ServerEnv: 服务端的环境.
	
* 两个框架基础封装: Ice看中服务的逻辑解耦, 部署上, 可以接受同机部署(同进程运行). 因此, 抽象出App这个概念, 来代表每个独立的应用, 在运行时以此划分上下文
    * Ice: 框架全局
    * App: 代表一个应用(一个独立的上下文)
	
* 一个资源管理机制: $app->proxy_resource
    * 任何面向连接的资源访问, 均可抽象两个步骤组成: 连接(含授权), 命令.(忽略关闭连接, 语言层面已经解决)
    * 对资源的管理, 比如熔断, 降级等, 是通用策略, 并且通常是应用在连接层面. (某些场景命令层面可提供数据)
    * 基于此, Ice将一个资源拆分为Connector和Handler两部分. 并在外层增加一个proxy调度层, 用作对资源的调度管理.
	
* 一个服务管理机制: $app->proxy_service
    * Ice大的层面, 分应用层(Web和Daemon运行方式下的入口)和服务层.
    * Ice强调服务的逻辑解耦, 部署上可灵活控制, 对应的, 通过服务的proxy屏蔽掉不同部署方式的调用细节.
	
* 一组框架基础工具集: Logger, Config
	
##  外围工具

* 一套基础工具库: Util. 期望将通用数据结构, 常用数据操作的算法等, 在Util中沉淀积累.
	
* 一套基于mysqli的数据库工具集: DB_Query. 提供过程化的SQL查询方法, 简化DB操作.
	
* 一套数据过滤工具集: $app->proxy_filter.
