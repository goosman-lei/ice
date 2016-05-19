<?php
namespace Ice\Frame\Web\TempEngine;
abstract class Abs {
    protected $response;

    protected $adapterConfig = array();
    protected $tempEngineConfig = array();

    public function __construct($response, $adapterConfig, $tempEngineConfig) {
        $this->response = $response;
        $this->adapterConfig = is_array($adapterConfig) ? $adapterConfig : array();
        $this->tempEngineConfig = is_array($tempEngineConfig) ? $tempEngineConfig : array();
        $this->initHeader();
        $this->init();
    }

    protected function initHeader() {
        if (isset($this->adapterConfig['headers'])) {
            foreach ($this->adapterConfig['headers'] as $header) {
                $this->response->addHeader($header);
            }
        }
    }

    protected function init() {
    }

    abstract public function clearAssign();
    abstract public function assign($datas);
    abstract public function display($tplPath);

    public function render() {
        $tplPath = strtolower($this->response->class . '/' . $this->response->action);
        $this->clearAssign();
        $this->assign($this->response->getTplData());
        return $this->display($tplPath);
    }
    
    public function renderError($errno, $data) {
        $tplPath = $this->adapterConfig['error_tpl'];
        $this->clearAssign();
        $this->assign(array(
            'code' => $errno,
            'data' => new \U_Map($data),
        ));
        return $this->display($tplPath);
    }
}
