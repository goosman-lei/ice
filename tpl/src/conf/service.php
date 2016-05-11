<?php
$pool = array(
    'demo-local' => array(
        'proxy'  => 'local',
        'config' => array(
            'project_group' => 'ice',
            'project_name'  => 'demo_service',
        ),
    ),
    'demo-remote' => array(
        'proxy'  => 'remote',
        'config' => array(
            'resource' => 'curl://service',
        ),
    ),
);
