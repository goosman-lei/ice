<?php
namespace Ice\Frame\Web\TempEngine;
class Json extends Abs {

    protected $tplDatas = array();

    public function clearAssign() {
        $this->tplDatas = array();
    }
    public function assign($datas) {
        $this->tplDatas = $datas;
    }
    public function display($tplPath) {
        $options = isset($this->adapterConfig['json_encode_options']) ? $this->adapterConfig['json_encode_options'] : 0;
        return json_encode($this->tplDatas, $options);
    }
}
