<?php
namespace demo\ui\Action\Say;
class Helloworld extends \FW_Action {
    public function execute() {
        echo '<pre>';
        print_r($this->request);
    }
}
