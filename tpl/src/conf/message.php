<?php
$default_class = "\\${PROJECT_NAMESPACE}\\Lib\\ServiceMessage";
$say = array(
    'hello' => array(
        'mode' => 'full',
        'config' => array(
            'server_room'          => 'pbs_zgc',
            'complete_sign_expire' => 86400,
            'id_gen_resource'      => 'redis://demo',
            'status_resource'      => 'redis://demo',
            'master_resource'      => 'rabbitmq://demo',
            'master_exchange'      => 'multi_server_room_master_exchange',
            'master_routingkey'    => 'master',
            'slave_resource'       => 'rabbitmq://demo',
            'slave_exchange'       => 'multi_server_room_slave_exchange',
            'slave_routingkey'     => 'slave',
        ),
    ),
);
