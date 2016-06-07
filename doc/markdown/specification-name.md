#  命名规范

* 所有类均遵循PSR4命名规范

* PROJECT_GROUP和PROJECT_NAME共同作用, 唯一定义一个项目

```
PROJECT_GROUP="ice"
PROJECT_NAME="demo"
```

* 项目均需定义在唯一的名字空间下, 建议直接使用PROJECT_GROUP和PROJECT_NAME组合构成顶层的两级名字空间

```
PROJECT_NAMESPACE="$PROJECT_GROUP\\$PROJECT_NAME"
```

* web应用的入口, 代码位于src/action. 内部保持两层结构(src/action/Controller/Action.php).

```
// 假定使用默认路由, 则URI /say/hi被解析到
src/action/Say/Hi.php

实现代码如下
namespace PROJECT_GROUP\PROJECT_NAME\Action\Say;
class Hi extends \FW_Action{
    public function execute() {
        return $this->success(array(
            'output' => 'Hi.'
        ));
    }
}
```

* daemon应用的入口, 代码位于src/daemon. 内部保持两层结构(src/action/Controller/Action.php)

```
调用命令:
    php src/daemon/cli.php --class=say --action=hi
将被解析到
src/daemon/Say/Hi.php

实现代码如下
namespace PROJECT_GROUP\PROJECT_NAME\Daemon\Say;
class Hi extends \FD_Daemon {
    public function execute() {
        return $this->output('Hi.');
    }
}
```

* service应用的入口, 代码位于src/service, 内部保持一层结构(src/action/Service.php)

```
对于服务say/hi
代码路径:
src/service/Say.php

示例实现代码如下
namespace PROJECT_GROUP\PROJECT_NAME\Service;
class Say extends \FS_Service {
    public function hi() {
        return 'Hi.';
    }
}
```

* 本地类库, 代码位于src/lib, 命名空间为PROJECT_GROUP\PROJECT_NAME\Lib

```
假定有本地类库Ip
则代码路径:
src/lib/Ip.php

示例实现代码如下:
namespace PROJECT_GROUP\PROJECT_NAME\Lib;
class Ip {
}
```

