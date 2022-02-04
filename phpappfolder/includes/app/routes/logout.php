<?php

/**
 * logout.php
 *
 * route for the log-out system
 *
 * Author: D Hollis
 * Email: <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/logout', function(Request $request, Response $response) use ($app)
{
    $m_db_wrapper = $app->getContainer()->get('DBWrapper');
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');

    $m_user_details = $m_db_wrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('user_id'));

    $m_user_logout = $m_session_wrapper->delete_session_token('user_id');

    //If session token is deleted, then logout
    if ($m_user_logout)
    {
        return $this->view->render($response,
            'login.html.twig',
            [
                'css_path' => CSS_PATH,
                'js_path' => JS_PATH,
                'static_path' => STATIC_PATH,
                'visitor_ip' => $_SERVER['REMOTE_ADDR'],
                'title' => 'Sign In',
                'action' => APP_URL . '/index.php',
                'message' => "You've signed out - goodbye " . $m_user_details['username'],
                'message_type' => 'info'
            ]);
    }

    //Else, redirect to home
    return $response->withRedirect(APP_URL . '/index.php');
})->setName('logout');