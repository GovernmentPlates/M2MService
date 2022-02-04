<?php

/**
 * signup.php
 *
 * index route for the log-in page
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

$app->get('/register', function(Request $request, Response $response) use ($app) {

    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    //If the user is already logged in, redirect them to the dashboard
    if ($m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    return $this->view->render($response,
        'register.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'action' => 'register',
            'title' => 'Sign Up'
        ]);
})->setName('register');

$app->post('/register', function(Request $request, Response $response) use ($app) {

    $m_user_wrapper = $app->getContainer()->get('UserWrapper');

    $m_form_submit_data = $request->getParsedBody();
    $p_form_username = $m_form_submit_data['username'];
    $p_form_email = $m_form_submit_data['email'];
    $p_form_password = $m_form_submit_data['password'];
    $p_form_phone = $m_form_submit_data['phone'];

    $m_user_registration = $m_user_wrapper->register_new_user($p_form_username, $p_form_email, $p_form_phone, $p_form_password);

    if ($m_user_registration)
    {
        return $this->view->render($response,
            'login.html.twig',
            [
                'css_path' => CSS_PATH,
                'js_path' => JS_PATH,
                'static_path' => STATIC_PATH,
                'visitor_ip' => $_SERVER['REMOTE_ADDR'],
                'action' => $_SERVER['SCRIPT_NAME'],
                'title' => 'Sign In',
                'message' => "You've signed up - please sign in",
                'message_type' => 'signup_success'
            ]);
    }

    return $this->view->render($response,
        'register.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'action' => 'register',
            'title' => 'Sign Up',
            'message' => "Registration failed - please try again",
            'message_type' => 'error'
        ]);
});