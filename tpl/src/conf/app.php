<?php
$namespace = '${PROJECT_NAMESPACE}';
$app_class = '\\Ice\\Frame\\App';

$root_path = __DIR__ . '/..';
$var_path  = $root_path . '/../var';
$run_path  = $var_path . '/run';
$log_path  = $var_path . '/logs';

@include(__DIR__ . '/web.inc');
@include(__DIR__ . '/service.inc');
@include(__DIR__ . '/daemon.inc');

$runner = array(
    'web' => array(
        'frame'       => $web_frame,
        'routes'      => $web_routes,
        'temp_engine' => $web_temp_engine,
        'log'         => $web_logger,
    ),
    'service' => array(
        'log' => $service_logger,
    ),
    'daemon' => array(
        'log'    => $daemon_logger,
        'filter' => $daemon_filter,
    ),
);

