<?php

/**
 * twofactorauth.php
 *
 * routes for the two factor authentication system
 *
 *
 * Author: D Hollis
 * Email: <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//Main route - show the two factor authentication page to the user
$app->get('/verification', function(Request $request, Response $response) use ($app) {

    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_db_wrapper = $app->getContainer()->get('DBWrapper');
    $m_user_session_already_logged_in = $m_session_wrapper->check_session_exists('user_id');

    //If the user is already logged in, redirect them to the dashboard
    if ($m_user_session_already_logged_in)
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    $m_2fa_session_exists = $m_session_wrapper->check_session_exists('2fa_session');

    //If the 2fa session doesn't exist, redirect the user to the login page
    if (!$m_2fa_session_exists) {
        return $response->withRedirect(APP_URL . '/');
    }

    $m_user_details = $m_db_wrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('2fa_session'));
    $m_2fa_wrapper = $app->getContainer()->get('TwoFactorWrapper');
    $m_generated_2fa_code = $m_2fa_wrapper->generate_2fa_code();
    $m_2fa_wrapper->insert_2fa_code($m_user_details['u_id'], $m_generated_2fa_code);
    $m_2fa_wrapper->send_2fa_code($m_user_details['phone'], $m_generated_2fa_code);

    return $this->view->render($response,
        'twofactor.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'phone' => $m_user_details['phone'],
            'action' => '2fa',
            'title' => '2FA Security Step'
        ]);
})->setName('twofa-auth');

//Route to obtain a new token
$app->get('/retry', function(Request $request, Response $response) use ($app) {

    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_db_wrapper = $app->getContainer()->get('DBWrapper');
    $m_user_session_already_logged_in = $m_session_wrapper->check_session_exists('user_id');

    if ($m_user_session_already_logged_in)
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    $m_2fa_session_exists = $m_session_wrapper->check_session_exists('2fa_session');

    if (!$m_2fa_session_exists) {
        return $response->withRedirect(APP_URL . '/');
    }

    $m_user_details = $m_db_wrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('2fa_session'));
    $m_2fa_wrapper = $app->getContainer()->get('TwoFactorWrapper');
    $m_generated_2fa_code = $m_2fa_wrapper->generate_2fa_code();
    $m_2fa_wrapper->insert_2fa_code($m_user_details['u_id'], $m_generated_2fa_code);
    $m_2fa_wrapper->send_2fa_code($m_user_details['phone'], $m_generated_2fa_code);
    
    return $this->view->render($response,
        'twofactor.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'phone' => $m_user_details['phone'],
            'action' => '2fa',
            'title' => '2FA Security Step',
            'message' => 'New one-time code sent',
            'message_type' => 'retry'
        ]);
})->setName('coderetry');

//Post route to verify the code and ultimately log the user in
$app->post('/2fa', function(Request $request, Response $response) use ($app) {
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_user_session_already_logged_in = $m_session_wrapper->check_session_exists('user_id');

    if ($m_user_session_already_logged_in)
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    $m_2fa_session_exists = $m_session_wrapper->check_session_exists('2fa_session');
    if (!$m_2fa_session_exists) {
        return $response->withRedirect(APP_URL . '/');
    }

    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_db_wrapper = $app->getContainer()->get('DBWrapper');
    $m_2fa_details = $m_db_wrapper->get_2fa_code_by_user_id($m_session_wrapper->get_session_token('2fa_session'));
    $m_user_details = $m_db_wrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('2fa_session'));
    $m_form_submit_data = $request->getParsedBody();
    $m_submitted_2fa_code = $m_form_submit_data['twofactor-code'];

    if (time() > $m_2fa_details['validTill'])
    {
        return $this->view->render($response,
            'twofactor.html.twig',
            [
                'css_path' => CSS_PATH,
                'js_path' => JS_PATH,
                'static_path' => STATIC_PATH,
                'visitor_ip' => $_SERVER['REMOTE_ADDR'],
                'phone' => $m_user_details['phone'],
                'action' => '2fa',
                'title' => '2FA Security Step',
                'message' => 'One-time code expired - try again',
                'message_type' => 'error'
            ]);
    }

    $m_2fa = $app->getContainer()->get('TwoFactorWrapper');
    $m_2fa_valid = $m_2fa->verify_2fa_code($m_user_details['u_id'], $m_submitted_2fa_code);

    if($m_2fa_valid)
    {
        $m_session_wrapper->delete_session_token('2fa_session');
        $m_session_wrapper->set_session_token('user_id', $m_user_details['u_id']);
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    return $this->view->render($response,
        'twofactor.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'phone' => $m_user_details['phone'],
            'action' => '2fa',
            'title' => '2FA Security Step',
            'message' => 'Invalid 2FA code',
            'message_type' => 'error'
        ]);
});