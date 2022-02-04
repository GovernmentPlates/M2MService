<?php

/**
 * settings.php
 *
 * route for the settings page
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

$app->get('/settings', function(Request $request, Response $response) use ($app)
{
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    if(!$m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/index.php');
    }

    $DBWrapper = $app->getContainer()->get('DBWrapper');
    //$UserWrapper = $app->getContainer()->get('UserWrapper');
    $m_user_details = $DBWrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('user_id'));

    return $this->view->render($response,
        'settings.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'title' => 'Account Settings',
            'admin' => $m_user_details['admin'],
            'username' => $m_user_details['username'],
            'email' => $m_user_details['email'],
            'phone' => $m_user_details['phone'],
            'action' => 'settings/changePass',

        ]);
})->setName('settings');

$app->post('/settings/changePass', function(Request $request, Response $response) use ($app)
{
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    if(!$m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/index.php');
    }

    $UserWrapper = $app->getContainer()->get('UserWrapper');
    $DBWrapper = $app->getContainer()->get('DBWrapper');
    $m_user_details = $DBWrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('user_id'));

    $m_form_submit_data = $request->getParsedBody();
    $m_form_current_password_data = $m_form_submit_data['currentPassword'];
    $m_form_new_password = $m_form_submit_data['newPassword'];
    $m_form_confirm_new_password = $m_form_submit_data['confirmNewPassword'];

    $changePass = $UserWrapper->change_user_password($m_form_current_password_data, $m_user_details['password'], $m_form_new_password, $m_form_confirm_new_password, $m_user_details['u_id']);

    return $this->view->render($response,
        'settings.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'title' => 'Account Settings',
            'admin' => $m_user_details['admin'],
            'username' => $m_user_details['username'],
            'email' => $m_user_details['email'],
            'phone' => $m_user_details['phone'],
            'password_change_status' => $changePass,
            'userPassHash' => $m_user_details['password'],
        ]);

})->setName('settings');