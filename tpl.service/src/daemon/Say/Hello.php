<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Say;
class Hello extends \FD_Daemon {
    public function execute() {
        $client = new \Ice\Frame\Service\Client('http://ice-service.leiguoguo.lab.niceprivate.com', 'Say');
        $this->output($client->hello('Daemon'));
        $this->output(json_encode($this->request->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->output(json_encode($this->request->argv, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
