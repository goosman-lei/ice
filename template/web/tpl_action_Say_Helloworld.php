<?php
namespace ${PROJECT_NAMESPACE}\Action\Say;
class Helloworld extends \FW_Action {
    public function execute() {
        return array(
            'code' => 0,
            'data' => array(
                'uid'   => 5012470,
                'uname' => 'goosman-lei',
            ),
        );
    }
}
