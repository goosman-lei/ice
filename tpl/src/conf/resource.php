<?php
$mapping = array(
    'connector' => array(),
    'handler'   => array(),
    'strategy'  => array(),
);
$pool = array(
    'mysqli' => array(
        'demo' => array(
            'master' => array(
                array('host' => '10.10.10.60', 'port' => 3306),
            ),
            'slave' => array(
                array('host' => '10.10.10.60', 'port' => 3306),
            ),
            'options' => array(
                'timeout' => 1,
                'user'    => 'nice',
                'passwd'  => 'Cb84eZaa229ddnm',
            ),
        ),
    ),
    'curl' => array(
        'service' => array(
            'default' => array(
                array('host' => 'ice-web.leiguoguo.lab.niceprivate.com')
            ),
        ),
    ),
);