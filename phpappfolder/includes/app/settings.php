<?php

/*
 * DBInterface.php
 *
 * Class to interact with the MariaDB server
 *
 * Author: Dominic Hollis, Artem Bobrov
 * Email: <p2533140@my365.dmu.ac.uk> <p2547788@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 * @author A Bobrov
 */

ini_set('display_errors', 'On');
ini_set('html_errors', 'On');
ini_set('xdebug.trace_output_name', 'session_example.%t');
date_default_timezone_set('Europe/London');

$app_url = dirname($_SERVER['SCRIPT_NAME']);
$css_path = $app_url . '/css/';
$js_path = $app_url . '/js/';
$static_path = $app_url . '/static/';
define('CSS_PATH', $css_path);
define('JS_PATH', $js_path);
define('STATIC_PATH', $static_path);
define('APP_URL', $app_url);

$settings = [
    "settings" => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'mode' => 'development',
        'debug' => true,
        'view' => [
            'template_path' => __DIR__ . '/templates/',
            'twig' => [
                'cache' => false,
                'auto_reload' => true,
            ]],
    ],
];

return $settings;