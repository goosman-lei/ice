<?php
$config = array(
    '*' => array(
    ),
    '/say/hello' => array(
        'is_local_access' => array(
            'ip eq 127.0.0.1',
        ),
    ),
);
