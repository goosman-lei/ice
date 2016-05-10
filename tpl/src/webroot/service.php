<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$runner = new \Ice\Frame\Runner\Service(__DIR__ . '/../conf/service_app.php');
$runner->run();
