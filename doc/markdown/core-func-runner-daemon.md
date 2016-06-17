# Daemon运行方式

![Daemon应用结构图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0005.ice-core-func-runner-daemon.png)

## Daemon

Daemon作为daemon运行方式的程序入口.

代码目录为: src/daemon/&lt;class&gt;/&lt;action&gt;.php

Daemon需要继承\FD_Daemon. 路由调度会自动执行Daemon的execute方法.

## Daemon的运行命令

```bash
# src/daemon/cli.php是daemon方式的执行入口

php src/daemon/cli.php --class=say --action=hello --opt=v arg1
```
