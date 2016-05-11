<?php
$pool = array(
    'demo-local' => array(
        'proxy'  => 'local',
        'config' => array(
            'project_group' => 'ice',
            'project_name'  => 'demo',
        ),
    ),
    'demo-remote' => array(
        'proxy'  => 'remote',
        'config' => array(
            'resource' => 'curl://service',
        ),
    ),
);
