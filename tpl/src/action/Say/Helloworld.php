<?php
namespace ${PROJECT_NAMESPACE}\Action\Say;
class Helloworld extends \FW_Action {
    public function execute() {
        $client = $this->ice->mainApp->proxy_service->get('demo-remote', 'Say');
        return array(
            'code' => 0,
            'data' => array(
                'uid'   => 5012470,
                'uname' => 'goosman-lei',
                'service' => $client->hello('Jack'),
            ),
        );
    }
}
