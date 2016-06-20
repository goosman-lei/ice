# 数据库访问

Ice中的数据库访问, 是基于mysqli实现的. 上层封装了一层方法, 用来简化SQL查询及防止SQL注入问题.

(不过, 直接的原生query()方法, 并没有做防SQL注入的处理, 因此, 要使用原生SQL, 请注意需要自己做防注入处理)

这属于一个典型的工具性场景, 因此, 不再赘述, 直接上应用DEMO

## 数据库访问组件的应用示例

### 应用层实现

```sql
# 对应表结构

CREATE TABLE IF NOT EXISTS user (
   id INT NOT NULL PRIMARY KEY,
   name VARCHAR(64) NOT NULL DEFAULT '',
   passwd VARCHAR(64) NOT NULL DEFAULT '',
   location VARCHAR(64) NOT NULL DEFAULT ''
) ENGINE Innodb DEFAULT CHARACTER SET UTF8;
```

```php
在model层实现, 继承\DB_Query

<?php
namespace <PROJECT_NAMESPACE>\Model;
class User extends \DB_Query {
    protected $tableName = 'user';  // 表名
    protected $mapping   = array(   // 字段映射, 支持类型: i(int), s(字符串), f(浮点), I(标识符: 字母数字下划线)
        'id'       => 'i',
        'name'     => 's',
        'passwd'   => 's',
        'location' => 's',
    );
    protected $dbResource = 'demo'; // 使用的资源. 对应src/conf/resource中的配置
}
```

### 查询多条记录

```php
$where = array(
    array('score', 80),   // :eq 操作符可忽略. 第二个元素是非数组, 默认:eq
    array('pass', array(TRUE, FALSE)), // :in 操作符可忽略. 第二个元素是数组默认:in
    array(':ne', 'score', 80),
    array(':gt', 'score', 80),
    array(':ge', 'score', 80),
    array(':le', 'score', 80),
    array(':lt', 'score', 80),
    array(':between', 'score', 80, 100),
    array(':lt', 'score', 8.3),
    array(':eq', 'name', 'Jack'),
    array(':like_literal', 'name', 'Jack%'),
    array(':like_begin', 'name', 'Jack%'),
    array(':like_end', 'name', 'Jack%'),
    array(':like_entire', 'name', 'Jack%'),
    array(':or', array(
        array(':notlike_literal', 'name', 'Jack_'),
        array(':notlike_begin', 'name', 'Jack_'),
        array(':notlike_end', 'name', 'Jack_'),
        array(':notlike_entire', 'name', 'Jack_'),
        array(':in', 'sex', array('man', 'woman')),
        array(':notin', 'color', array('blue', 'black')),
    )),
    array(':not', array(
        array(':notnull', 'money'),
    )),
    array(':and', array(
        array(':isnull', 'color'),
    )),
    array(':literal', 'uid = 100'),
);
$datas = $model->getRows($where, '*', 20, 5, 'score DESC');

对应SQL:
SELECT * FROM <TABLE>
    WHERE (`score` = 80)
         AND (`pass` IN (1,0))
         AND (`score` != 80)
         AND (`score` > 80)
         AND (`score` >= 80)
         AND (`score` <= 80)
         AND (`score` < 80)
         AND (`score` BETWEEN 80 AND 100)
         AND (`score` < 8)
         AND (`name` = 'Jack')
         AND (`name` LIKE 'Jack%')
         AND (`name` LIKE 'Jack%'%)
         AND (`name` LIKE %'Jack%')
         AND (`name` LIKE %'Jack%'%)
         OR ((`name` NOT LIKE 'Jack_')
             AND (`name` NOT LIKE 'Jack_'%)
             AND (`name` NOT LIKE %'Jack_')
             AND (`name` NOT LIKE %'Jack_'%)
             AND (`sex` IN ('man','woman'))
             AND (`color` NOT IN ('blue','black'))
         )
         AND NOT ((`money` IS NOT NULL))
         AND ((`color` IS NULL))
         AND (uid = 100)
    ORDER BY score DESC
    limit 5, 20


getRow(): 参数除$limit和$offset外, 和getRows()一致, 强制limit 0, 1
count():  使用count()聚集函数获取行数
```

### 更新

```php
# UPDATE <TABLE> SET name = 'goosman-lei', id = 5012470, age = age + 1 WHERE id = 5012469

$model->update(array(
    array('name', 'goosman-lei'),
    array('id', 5012470),
    array(':literal', 'age = age+1'),
), array(':eq', 'id', 5012469));
```

### 插入

```php
# INSERT INTO <TABLE>(id, name) VALUES(5012470, 'goosman-lei') ON DUPLICATE KEY UPDATE name = 'goosman-lei'

$model->insert(array(
    array('id', 5012470),
    array('name', 'goosman-lei'),
), array('name'));

# INSERT INTO <table>(id, name) VALUES(5012470, 'goosman-lei'), (5012471, 'goosman.lei')

$model->multiInsert(array(
    array(
        array('id', 5012470),
        array('name', 'goosman-lei'),
    ),
    array(
        array('id', 5012471),
        array('name', 'goosman.lei'),
    ),
));
```

### Replace

```php
# REPLACE INTO <table>(id, name) VALUES(5012470, 'goosman-lei')

$model->replace(array(
    array('id', 5012470),
    array('name', 'goosman-lei'),
));
```

### 删除

```php
# DELETE FROM <table> WHERE id >= 1000 limit 10

$model->delete(array(':ge', 'id', 1000), FALSE, 10)
```

### 原生接口

```php
# SELECT * FROM user WHERE name = 'goosman-lei'
# 返回关联数组
$model->query('SELECT * FROM user WHERE name = ' . $model->escapeString('goosman-lei'), \DB_Query::RS_ARRAY);

# SELECT * FROM user WHERE name = 'goosman-lei'
# 返回数字下标数组
$model->query('SELECT * FROM user WHERE name = ' . $model->escapeString('goosman-lei'), \DB_Query::RS_NUM);

# SELECT * FROM user WHERE name LIKE 'goosman-lei'
$model->query('SELECT * FROM user WHERE name LIKE ' . $model->escapeString('goosman-lei', TRUE));

# SELECT * FROM user WHERE name LIKE '%goosman-lei%'
$model->query("SELECT * FROM user WHERE name LIKE '%" . $model->escapeString('goosman-lei', TRUE, FALSE) . "%'");

# UPDATE user SET name = 'goosman-lei' WHERE id = 5012470
$model->execute("UPDATE user SET name = 'goosman-lei' WHERE id = 5012470");
```

### 获取额外数据

```php
$model->affectedNum(): 获取上一条SQL的影响行数
$model->lastId():      获取最后一次插入的自增ID值
```
