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
        $this->serverEnv  = new \Ice\Frame\Embeded\ServerEnv();
        $this->clientEnv  = new \Ice\Frame\Embeded\ClientEnv();
        $this->clientEnv->ip = $this->options['client_ip'];
    }

    protected function setupRequest() {
        $this->request = new \Ice\Frame\Embeded\Request();
        $this->request->id     = $this->options['request_id'];
        $this->request->class  = $this->options['class'];
        $this->request->action = $this->options['action'];
    }

    protected function setupResponse() {
        $this->response = new \Ice\Frame\Embeded\Response();
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
