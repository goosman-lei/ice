# Filter模块

![Filter模块设计图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0003.ice-core-filter-design.png)

##  设计目标

* 描述业务数据结构
* 验证输入数据, 修正输出数据
* 作为输入输出数据的统一出入口, 提供HOOK价值

## 语法定义

```
LITERAL_ID      := REGEXP( [-_a-zA-Z0-9]+ )
LITERAL_STRING  := "\"" REGEXP( .* ) "\"" | "'" REGEXP( .* ) "'"
LITERAL_NUMERIC := [ "-" ] REGEXP( [0-9]+ ) [ "." REGEXP( [0-9]+ ) ]

OP_NAME        := LITERAL_ID
FIELD_NAME     := LITERAL_ID
TYPE_NAME      := LITERAL_ID
OP_ARG         := LITERAL_STRING | LITERAL_ID | LITERAL_NUMERIC
REQ_OR_DEFAULT := "__opt" | "__req" | LITERAL_STRING | LITERAL_ID | LITERAL_NUMERIC

TYPE        := "(" TYPE_NAME [ ":" REQ_OR_DEFAULT ] ")"
OP_ARG_LIST := OP_ARG | OP_ARG "," OP_ARG_LIST

FIELD_RULE_NONAME := TYPE FILTER_LIST
FIELD_RULE        := FIELD_NAME FIELD_RULE_NONAME
FIELD_RULE_LIST   := FIELD_RULE [ ";" ] | FIELD_RULE ";" FIELD_RULE_LIST

BLOCK_FILTER  := "{" FIELD_RULE_LIST "}"
EXTEND_FILTER := "@" LITERAL_STRING
OP_FILTER     := OP_NAME [ ":" OP_ARG_LIST ]

FILTER_LIST := BLOCK_FILTER | EXTEND_FILTER | OP_FILTER | BLOCK_FILTER FILTER_LIST | ( EXTEND_FILTER | OP_FILTER ) [ "|" ] FILTER_LIST

ROOT := FIELD_RULE_NONAME

```

## 应用示例

```php
$data = array(
    'code' => 0,
    'data' => array(
        'uid'     => 5012470,
        'uname'   => 'goosman-lei',
        'service' => array('code' => 1, 'data' => 'Hello Jack'),
        'user'    => array('id' => '5012470', 'name' => 'goosman-lei', 'location' => '北京'),
    ),
);
$filteredData = $this->ice->mainApp->proxy_filter->get('(map){
    code(int);
    data(map){
        uid(int);
        uname(str);
        service(map){
            code(int);
            data(str);
        };
        user(map){
            id(int);
            name(str);
            location(str);
        }
    }
}')->filter($data);
```

