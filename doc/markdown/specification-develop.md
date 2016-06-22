# 开发规范

* mainApp和workApp的定义及应用规则

```
mainApp指启动应用的项目.
workApp是当前调用上下文所在的项目. (在以local方式调用service时, 会自动切换workApp到被调用service项目)

应用时的选择规则:
* 记录日志: 除非你明确为什么要使用workApp的配置记录日志, 否则使用mainApp->logger_xxx
* 读取配置: 除非你明确为什么要使用mainApp的配置信息, 否则使用workApp->config->get('a.b')
* 使用3类proxy组件: 全部使用workApp->proxy_xxx(filter, resource, service)
```

* 不允许产生跨层调用, 合法的调用层次关系如下

```
action => service
daemon => service
service => model
[ action | daemon | service | model ] => lib
[ action | daemon | service | model | lib ] => 框架
```

* 仅daemon/action中可以使用四种输入输出数据对象

