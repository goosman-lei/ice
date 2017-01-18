<?php
namespace Ice\Frame\Web\TempEngine;
class Smarty extends Abs {

    protected $smarty;

    protected function init() {
        $this->smarty = new \Smarty();
        foreach ($this->tempEngineConfig as $k => $v) {
            if ($k == 'template_dir') {
                $this->smarty->setTemplateDir($v);
            } else if ($k == 'compile_dir') {
                $this->smarty->setCompileDir($v);
            } else if ($k == 'config_dir') {
                $this->smarty->setConfigDir($v);
            } else if ($k == 'cache_dir') {
                $this->smarty->setCacheDir($v);
            } else if ($k == 'plugin_dir') {
                $this->smarty->setPluginsDir($v);
            } else {
                $this->smarty->$k = $v;
            }
        }
    }

    public function clearAssign() {
        return $this->smarty->clearAllAssign();
    }
    public function assign($datas) {
        return $this->smarty->assign($datas);
    }
    public function display($tplPath) {
        $tplPath = isset($this->adapterConfig['ext_name']) ? $tplPath . $this->adapterConfig['ext_name'] : $tplPath;
        return $this->smarty->fetch($tplPath, NULL, NULL, NULL, FALSE);
    }
}
