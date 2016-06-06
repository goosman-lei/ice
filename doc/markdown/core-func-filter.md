# Filter模块

![Filter模块设计图](https://raw.githubusercontent.com/goosman-lei/ice/master/doc/resource/images/0003.ice-core-filter-design.png)

##  设计目标

* 描述业务数据结构
* 验证输入数据, 修正输出数据
* 作为输入输出数据的统一出入口, 提供HOOK价值

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

