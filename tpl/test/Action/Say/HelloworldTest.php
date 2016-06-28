<?php
namespace ${PROJECT_NAMESPACE}\UT\Action\Say;
class Helloworld extends \FW_UT {
    /**
     * @test
     */
    public function demo() {
        $data = $this->callAction('Say', 'helloworld');
        $this->assertArrayHasKey('code', $data);
    }
}
