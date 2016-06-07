# 开发规范

* workApp和mainApp

```
mainApp指启动应用的项目.
workApp是当前调用上下文所在的项目. (在以local方式调用service时, 会自动切换workApp到被调用service项目)

因此, 使用App对象的3种场景, 按照如下规则选择:
1. 记录日志: 除非明确要使用workApp的日志配置, 否则使用mainApp->logger_xxx记录日志.
2. 使用3类组件: 必须使用workApp->proxy_filter, workApp->proxy_resource, workApp->proxy_service调用. (因为依赖关系是一个层次结构配置而非全局)
3. 读取配置: 除非明确要使用入口应用配置, 否则使用workdApp->config.
```

* 请不要产生跨层调用, 正确的调用层次关系是

```
action  => service
daemon  => service
service => model

action  => lib
daemon  => lib
service => lib
model   => lib
```

* 仅daemon/action下可以使用四种输入输出数据对象

