<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$runner = new \Ice\Frame\Runner\Daemon(__DIR__ . '/../conf/app.php');
$runner->run();
