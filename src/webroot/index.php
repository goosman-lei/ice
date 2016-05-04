<?php
ini_set('display_errors', 'on');
require_once __DIR__ . '/../../vendor/autoload.php';
$runner = new \Ice\Frame\Runner\Web(realpath(__DIR__ . '/..'));
$runner->run();
