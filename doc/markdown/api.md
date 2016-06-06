# API

* daemon的输入输出数据对象接口

```
$this->clientEnv 无可用信息

$this->serverEnv->hostname;
$this->serverEnv->argc; // 所有$_SERVER内变量均以映射到此对象

$this->request->options['option_name']; // 所有--long-opt=xxx -antp -n=1等标准UNIX命令行选项均解析到此数组
$this->request->argv[0];                // 所有除选项外的命令行参数, 均解析到此数组
$this->request->stdin;                  // 标准输入文件资源
$this->request->originalArgv;           // 原始命令行参数列表
$this->request->id;                     // 请求ID
$this->request->getOption($name, $default = null);
$this->request->hadOption($name);
$this->request->class;                  // 路由后的controller
$this->request->action;                 // 路由后的action

$this->response->stdout;                // 标准输出文件资源
$this->response->stderr;                // 标准错误文件资源
$this->response->class;                 // 路由后的controller
$this->response->action;                // 路由后的action

```

* web action的输入输出数据对象接口

```
$this->clientEnv->ip                    // 客户端IP地址

$this->serverEnv->hostname;
$this->serverEnv->argc; // 所有$_SERVER内变量均以映射到此对象

$this->request->getParams();            // 自定义路由设置的参数
$this->request->getQueries();           // 获取所有GET参数
$this->request->getPosts();             // 获取所有POST参数
$this->request->getCookies();           // 获取所有COOKIE
$this->request->getFiles();             // 获取所有上传文件结构
$this->request->getParam($name, $default = null);   // 获取单个自定义路由设置的参数
$this->request->getQuery($name, $default = null);   // 获取单个GET参数
$this->request->getPost($name, $default = null);    // 获取单个POST参数
$this->request->getCookie($name, $default = null);  // 获取单个Cookie
$this->request->getFile($name, $default = null);    // 获取单个上传文件
$this->request->getBody();              // 获取请求BODY
$this->request->id;                     // 请求ID
$this->request->uri;                    // 经过优化处理之后的URI
$this->request->originalUri;            // 原始请求URI
$this->request->class;                  // 路由后的controller
$this->request->action;                 // 路由后的action

$this->response->output();                         // 执行正常流的模板引擎输出逻辑
$this->response->error($errno, $data = array());   // 执行异常流的模板引擎输出逻辑
$this->response->appendBody($string);   // 向输出的body中添加原始字符串
$this->response->setBody($string);      // 设置输出body
$this->response->addHeader($header);    // 添加响应HEADER
$this->response->addCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = FALSE, $httponly = FALSE); // 添加响应时应用的Cookie
$this->response->class;                 // 路由后的controller
$this->response->action;                // 路由后的action
```
