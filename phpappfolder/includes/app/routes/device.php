<?php
/**
 * device.php
 *
 * route for the device page
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

$app->get('/device', function(Request $request, Response $response) use ($app)
{
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    if(!$m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/index.php');
    }

    $DBWrapper = $app->getContainer()->get('DBWrapper');
    $m_user_details = $DBWrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('user_id'));

    if($m_user_details['admin'])
    {
        $SMSWrapper = $app->getContainer()->get('SMSWrapper');
        $SMSWrapper->parse_messages(10, $m_user_details['phone']);
    }

    $m_latest_device_update_details = $DBWrapper->get_last_message();
    $m_device_temp_details = $DBWrapper->get_all_temp();
    $m_device_date_details = $DBWrapper->get_all_message_received_dates();

    if($m_latest_device_update_details['switch1']) $m_switch1 = 'On';
    else $m_switch1 = 'Off';

    if($m_latest_device_update_details['switch2']) $m_switch2 = 'On';
    else $m_switch2 = 'Off';

    if($m_latest_device_update_details['switch3']) $m_switch3 = 'On';
    else $m_switch3 = 'Off';

    if($m_latest_device_update_details['switch4']) $m_switch4 = 'On';
    else $m_switch4 = 'Off';

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
        'device.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'title' => 'Device Status',
            'static_path' => STATIC_PATH,
            'admin' => $m_user_details['admin'],
            'deviceLastUpdate' => $m_latest_device_update_details['receivedDate'],
            'deviceFanConfig' => $m_device_fan_configuration,
            'deviceFanStatus' => $m_device_fan_status,
            'deviceLastKeypadInput' => $m_latest_device_update_details['keypadLastValue'],
            'deviceTemp' => $m_latest_device_update_details['heaterTemperature'],
            'deviceGraph_Temp' => $m_device_temp_details,
            'deviceGraph_DateTime' => $m_device_date_details,
            'switch1' => $m_switch1,
            'switch2' => $m_switch2,
            'switch3' => $m_switch3,
            'switch4' => $m_switch4
        ]);
})->setName('device');