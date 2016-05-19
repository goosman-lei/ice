<?php
namespace ice\home\Lib\Base;
class Action extends \FW_Action {
    public function execute() {
        $this->init();

        return $this->realExecute();
    }

    protected function init() {
    }
}
