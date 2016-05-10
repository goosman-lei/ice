<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$runner = new \Ice\Frame\Runner\Daemon(realpath(__DIR__ . '/..'));
$runner->run();
