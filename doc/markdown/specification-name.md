#  命名规范

* 所有类均遵循PSR4命名规范

* PROJECT_GROUP和PROJECT_NAME共同作用, 唯一定义一个项目.(composer.json中的项目名与此保持一致)

```
PROJECT_GROUP="ice"
PROJECT_NAME="demo"
```

* 项目均需定义在唯一的名字空间下, 建议直接使用PROJECT_GROUP和PROJECT_NAME组合构成顶层的两级名字空间

```
PROJECT_NAMESPACE="$PROJECT_GROUP\\$PROJECT_NAME"
```

* 入口代码名字规范

```
# web runner
# Action为入口, 代码位于src/action/Class/Action.php
# 名字空间: PROJECT_GROUP\PROJECT_NAME\Action

namespace PROJECT_GROUP\PROJECT_NAME\Action\Class;
class Action extends \FW_Action {
    public function execute() {
        // 业务逻辑
    }
}

# daemon runner
# Daemon为入口, 代码位于src/daemon/Class/Daemon.php
# 名字空间: PROJECT_GROUP\PROJECT_NAME\Daemon

namespace PROJECT_GROUP\PROJECT_NAME\Daemon\Class;
class Daemon extends \FD_Daemon {
    public function execute() {
        // 业务逻辑
    }
}

# service runner
# Service为入口, 代码位于src/service/Service.php
# 名字空间: PROJECT_GROUP\PROJECT_NAME\Service

namespace PROJECT_GROUP\PROJECT_NAME\Service;
class Service extends \FS_Service {
    public function hi() {
        // 业务逻辑
        return $this->succ();
    }
}
```

* model层

```
# 代码位于src/model
# 名字空间: PROJECT_GROUP\PROJECT_NAME\Model

namespace PROJECT_GROUP\PROJECT_NAME\Model;
class User extends \DB_Query {
    protected $tableName; # 表名
    protected $mapping = array(
        'field_name' => 'field_type',   # field_type = i(int), s(string), I([a-zA-Z0-9_]+), f(float)
    );
    protected $dbResource; # 使用的数据库连接资源
}
```

* 本地类库

```
# 代码位于src/lib
# 名字空间PROJECT_GROUP\PROJECT_NAME\Lib

namespace PROJECT_GROUP\PROJECT_NAME\Lib;
class Demo {
}
```

