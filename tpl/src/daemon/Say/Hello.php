<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Say;
class Hello extends \FD_Daemon {
    public function execute() {
        $this->output('Hello');
        $this->output(json_encode($this->request->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->output(json_encode($this->request->argv, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
