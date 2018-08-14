<?php
namespace Ice\Frame\Runner;
class Embeded {
    public $name = 'embeded';

    protected $rootPath;

    // input data
    public $serverEnv;
    public $clientEnv;
    public $request;
    public $input;

    // static info
    public $mainAppConf = array(
        'namespace' => '',
        'app_class' => '\\Ice\\Frame\\App',
    );

    // output data
    public $response;

    // context
    public $ice;

    protected $options = array();

    /**
     * embed 
     * @param mixed $options 
        $options  = array(
            'client_ip'  => '0.0.0.0', # 客户端IP地址
            'class'      => 'index',   # controller类, 无意义, 仅用作日志
            'action'     => 'index',   # action方法, 无意义, 仅用作日志
            'request_id' => '',        # 请求ID, 可选
        );
     * @param mixed $config 
        $config = array(
            'root_path' => __DIR__ . '/..',
            'var_path'  => $root_path . '/../var',
            'run_path'  => $var_path . '/run',
            'log_path'  => $var_path . '/logs',
            'conf_path' => $root_path . '/conf, # 配置文件路径

            'runner' => array(
                'log'    => $service_logger,
            ),
        );
     * @static
     * @access public
     * @return void
     */
    public static function embed($options, $config) {
        $runner = new self($options, $config);

        $runner->run();
    }

    protected function __construct($options, $config) {
        $this->options     = $options;
        $this->mainAppConf = array_merge($this->mainAppConf, $config);
        $this->rootPath    = $config['root_path'];
    }

    public function run() {
        $this->initIce();

        $this->setupEnv();
        $this->setupRequest();
        $this->setupResponse();

        $this->setupIce($this);
    }

    protected function setupEnv() {
        $serverEnvClass = isset($this->mainAppConf['runner']['frame']['server_env_class'])
                        ? $this->mainAppConf['runner']['frame']['server_env_class']
                        : '\\Ice\\Frame\\Embeded\\ServerEnv';
        $clientEnvClass = isset($this->mainAppConf['runner']['frame']['client_env_class'])
                        ? $this->mainAppConf['runner']['frame']['client_env_class']
                        : '\\Ice\\Frame\\Embeded\\ClientEnv';
        $this->serverEnv  = new $serverEnvClass();
        $this->clientEnv  = new $clientEnvClass();
        $this->clientEnv->ip = $this->options['client_ip'];
    }

    protected function setupRequest() {
        $requestClass = isset($this->mainAppConf['runner']['frame']['request_class'])
                        ? $this->mainAppConf['runner']['frame']['request_class']
                        : '\\Ice\\Frame\\Embeded\\Request';
        $this->request = new $requestClass();
        $this->request->id     = $this->options['request_id'];
        $this->request->class  = $this->options['class'];
        $this->request->action = $this->options['action'];
    }

    protected function setupResponse() {
        $responseClass = isset($this->mainAppConf['runner']['frame']['response_class'])
                        ? $this->mainAppConf['runner']['frame']['response_class']
                        : '\\Ice\\Frame\\Embeded\\Response';
        $this->response = new $responseClass();
        $this->response->class  = $this->options['class'];
        $this->response->action = $this->options['action'];
    }

    protected function initIce() {
        $this->ice = \F_Ice::init($this, $this->rootPath);
    }

    protected function setupIce() {
        $this->ice->setup();
    }
}
