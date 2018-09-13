<?php
namespace Ice\Frame\Runner;
class Service {
    public $name = 'service';

    protected $rootPath;

    // input data
    public $serverEnv;
    public $clientEnv;
    public $request;
    public $input;

    // static info
    public $mainAppConf;

    // output data
    public $response;

    // context
    public $ice;

    public function __construct($confPath) {
        $this->rootPath = realpath(dirname($confPath) . '/..');
        $this->mainAppConf = \F_Config::getConfig($confPath);
        $this->mainAppConf['runner'] = $this->mainAppConf['runner']['service'];
    }

    public function run() {
        $this->initIce();

        $this->setupEnv();
        $this->setupRequest();
        $this->setupResponse();

        $this->setupIce($this);

        $this->dispatch();
    }

    protected function setupEnv() {
        $this->serverEnv  = new \Ice\Frame\Service\ServerEnv();
        $this->clientEnv  = new \Ice\Frame\Service\ClientEnv();
    }

    protected function setupRequest() {
        $this->input   = file_get_contents('php://input');
        $this->request = \Ice\Frame\Service\ProtocolJsonV1::decodeRequest($this->input);
    }

    protected function setupResponse() {
        $this->response = new \Ice\Frame\Service\Response();
        if (is_object($this->request)) {
            $this->response->id = $this->request->id;
        }
    }

    protected function initIce() {
        $this->ice = \F_Ice::init($this, $this->rootPath);
    }

    protected function setupIce() {
        $this->ice->setup();
    }

    protected function dispatch() {
        if (!is_object($this->request)) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'input'  => $this->input,
                'msg'    => 'parse request failed',
            ), $this->request);
            return $this->response->error($this->request);
        }

        try {
            $className = "\\{$this->mainAppConf['namespace']}\\Service\\{$this->request->class}";

            if (!class_exists($className) || !method_exists($className, $this->request->action)) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or action',
                ), \F_ECode::UNKNOWN_URI);
                return $this->response->error(\F_ECode::UNKNOWN_URI, array(
                    'class'  => $this->request->class,
                    'action' => $this->request->action,
                    'msg'    => 'dispatch error: no class or action',
                ));
            }

            $serviceObj = new $className();
            $serviceObj->setIce($this->ice);

            $serviceObj->message = \Ice\Message\Factory::factory($this->request->class, $this->request->action, $this->request->params);

            $result = call_user_func_array(array($serviceObj, $this->request->action), $this->request->params);
            $code = isset($result['code']) ? $result['code'] : \F_ECode::WS_ERROR_RESPONSE;
            $data = isset($result['data']) ? $result['data'] : null;

            //记录请求API日志
            if(isset(\F_Ice::$ins->workApp->logger_api)){
                \F_Ice::$ins->workApp->logger_api->info(array(
                    'api'    => $this->request->class.'/'.$this->request->action,
                    'query'  => $this->request->params,
                    'result' => $code,
                    'respTime' => number_format(floatval(microtime(TRUE) -  $this->serverEnv->REQUEST_TIME_FLOAT) * 1000, 2) . 'ms',
                ));
            }

            $this->response->output($code, $data);
        } catch (\Exception $e) {
            $error = array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            );
            \F_Ice::$ins->mainApp->logger_comm->fatal($error, \F_ECode::PHP_ERROR);
            $this->response->error(\F_ECode::PHP_ERROR, $error);
        }
    }
}
