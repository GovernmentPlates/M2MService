<?php

/**
 * dashboard.php
 *
 * route for the dashboard page
 *
 *
 * Author: D Hollis, A Bobrov
 * Email: <p2533140@my365.dmu.ac.uk> <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 * @author A Bobrov
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/dashboard', function (Request $request, Response $response, array $args) use ($app) {
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    if(!$m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/index.php');
    }

    $m_user_session_id = $m_session_wrapper->get_session_token('user_id');
    $m_user_details = $app->getContainer()->get('DBWrapper')->retrieve_user_by_id($m_user_session_id);
    $m_latest_device_update_details = $app->getContainer()->get('DBWrapper')->get_last_message();
    $m_device_temp_details = $app->getContainer()->get('DBWrapper')->get_all_temp();
    $m_device_date_details = $app->getContainer()->get('DBWrapper')->get_all_message_received_dates();

    if ($m_latest_device_update_details['fanForward']) {
        $m_device_fan_configuration = "Forward";
    } else if ($m_latest_device_update_details['fanReverse']) {
        $m_device_fan_configuration = "Reverse";
    } else {
        $m_device_fan_configuration = "No config";
    }

    if (!($m_latest_device_update_details['fanEnabled'])) {
        $m_device_fan_status = "Off";
    } else {
        $m_device_fan_status = "On";
    }

    return $this->view->render($response,
        'dashboard.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'title' => 'Dashboard Home',
            'static_path' => STATIC_PATH,
            'username' => $m_user_details['username'],
            'admin' => $m_user_details['admin'],
            'deviceLastUpdate' => $m_latest_device_update_details['receivedDate'],
            'deviceFanConfig' => $m_device_fan_configuration,
            'deviceFanStatus' => $m_device_fan_status,
            'deviceLastKeypadInput' => $m_latest_device_update_details['keypadLastValue'],
            'deviceTemp' => $m_latest_device_update_details['heaterTemperature'],
            'deviceGraph_Temp' => $m_device_temp_details,
            'deviceGraph_DateTime' => $m_device_date_details
        ]);
})->setName('dashboard');