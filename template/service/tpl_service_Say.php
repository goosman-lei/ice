<?php
namespace ${PROJECT_NAMESPACE}\Service;
class Say extends \FS_Service {
    public function hello($name) {
        return $this->succ('Hello ' . $name);
    }
}
