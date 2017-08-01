<?php
namespace Ice\Frame\Web\TempEngine;
class Phtml extends Abs {

    protected $tplDatas = array();

    public function clearAssign() {
        $this->tplDatas = array();
    }
    public function assign($datas) {
        $this->tplDatas = $datas;
    }
    public function display($tplPath) {
        $content = '';
        
        //模板目录
        $tplDir = \F_Ice::$ins->workApp->rootPath . DIRECTORY_SEPARATOR . 'tpl';
        if(isset($this->tempEngineConfig['template_dir'])){
            $tplDir = $this->tempEngineConfig['template_dir'];
        }
        
        //后缀名
        $extName = '.phtml';
        if(isset($this->adapterConfig['ext_name'])){
            $extName = $this->adapterConfig['ext_name'];
        }
        
        //模板文件存在则渲染
        $tplRealPath = realpath($tplDir . $tplPath . $extName);
        if(file_exists($tplRealPath)){
            ob_start();
            extract($this->tplDatas);
            @include($tplRealPath);
            $content = ob_get_clean();
            ob_flush();
        }
        return $content;
    }
}
