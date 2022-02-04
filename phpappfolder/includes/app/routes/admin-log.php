<?php

/**
 * admin-log.php
 *
 * route for the admin-only area log page
 *
 * Author: D Hollis, A Bobrov, A Grosvenor
 * Email: <p2533140@my365.dmu.ac.uk> <p2547788@my365.dmu.ac.uk> <p2573368@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 * @author A Bobrov
 * @author A Grosvenor
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/admin/log', function(Request $request, Response $response) use ($app)
{
    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    if(!$m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/index.php');
    }

    $DBWrapper = $app->getContainer()->get('DBWrapper');
    $m_user_details = $DBWrapper->retrieve_user_by_id($m_session_wrapper->get_session_token('user_id'));

    // check if current user is admin - if not, redirect to dashboard
    if(!$m_user_details['admin'])
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

    $m_log_entry = $DBWrapper->get_all_log_entries();

    return $this->view->render($response,
        'admin-log.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'admin' => $m_user_details['admin'],
            'title' => 'Admin Log',
            'log_entry' => $m_log_entry
        ]);
})->setName('admin-log');