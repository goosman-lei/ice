<?php
namespace ${PROJECT_NAMESPACE}\UT\Action\Say;
require_once __DIR__ . '/../../../vendor/autoload.php';
class Helloworld extends \FW_UT {
    /**
     * @test
     */
    public function demo() {
        $data = $this->callAction('Say', 'Helloworld');
        $this->assertArrayHasKey('code', $data);
    }
}
