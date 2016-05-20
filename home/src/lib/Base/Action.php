<?php
namespace ice\home\Lib\Base;
class Action extends \FW_Action {
    protected $markdown;

    public function execute() {
        $this->init();

        return $this->realExecute();
    }

    protected function init() {
        $this->markdown = new \H_Markdown();
    }
}
