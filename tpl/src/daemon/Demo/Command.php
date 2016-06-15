<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Demo;
class Command extends \FD_Daemon {
    public function execute() {
        $this->output(json_encode($this->request->argv, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
