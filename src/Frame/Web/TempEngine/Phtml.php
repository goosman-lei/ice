<?php
namespace Ice\Frame\Web\TempEngine;
class Phtml extends Abs {

    protected $tplDatas = array();
    protected $tplRealPath = '';

    public function clearAssign() {
        $this->tplDatas = array();
    }
    public function assign($datas) {
        $this->tplDatas = $datas;
    }
    public function display($tplPath) {
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
        $this->tplRealPath = realpath($tplDir . $tplPath . $extName);
        return $this->renderTpl();
    }
    
    private function renderTpl(){
        if(file_exists($this->tplRealPath)){
            ob_start();
            extract($this->tplDatas);
            @include($this->tplRealPath);
            $content = ob_get_clean();
        }else{
            $content = '';
        }
        return $content;
    }
}
