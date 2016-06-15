<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Demo;
class Service extends \FD_Daemon {
    public function execute() {
        $proxy = $this->ice->workApp->proxy_service->get('internal', 'Say');
        $this->output($proxy->hello('Daemon'));
        $this->output(json_encode($this->request->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
