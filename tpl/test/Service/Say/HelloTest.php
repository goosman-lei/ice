<?php
namespace ${PROJECT_NAMESPACE}\UT\Action\Say;
require_once __DIR__ . '/../../../vendor/autoload.php';
class Hello extends \FW_UT {
    /**
     * @test
     */
    public function demo() {
        $proxy = \F_Ice::$ins->workApp->proxy_service->get('internal', 'Say');
        $data  = $proxy->hello('Jack');
        $this->assertArrayHasKey('code', $data);
    }
}
