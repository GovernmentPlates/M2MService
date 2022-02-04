<?php
/**
 * login.php
 *
 * index route for the log-in page
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

$c_user_wrapper = new \User\UserProcessor();
$c_db_wrapper = new \DB\DBInterface();

$app->get('/', function(Request $request, Response $response) use ($app) {

    $m_session_wrapper = $app->getContainer()->get('SessionWrapper');
    $m_session_exists = $m_session_wrapper->check_session_exists('user_id');

    //If the user is already logged in, redirect them to the dashboard
    if ($m_session_exists)
    {
        return $response->withRedirect(APP_URL . '/dashboard');
    }

  return $this->view->render($response,
    'login.html.twig',
    [
      'css_path' => CSS_PATH,
      'js_path' => JS_PATH,
      'static_path' => STATIC_PATH,
      'visitor_ip' => $_SERVER['REMOTE_ADDR'],
      'action' => $_SERVER['SCRIPT_NAME'],
      'title' => 'Sign In'
    ]);
})->setName('login');

$app->post('/', function(Request $request, Response $response) use ($c_user_wrapper, $c_db_wrapper) {
    $m_form_submit_data = $request->getParsedBody();
    $p_form_email = $m_form_submit_data['email'];
    $p_form_password = $m_form_submit_data['password'];

    $m_user_auth = $c_user_wrapper->authenticate_user($p_form_email, $p_form_password);


    if($m_user_auth) {
        return $response->withRedirect(APP_URL . '/verification'); // /dashboard
    }

    return $this->view->render($response,
        'login.html.twig',
        [
            'css_path' => CSS_PATH,
            'js_path' => JS_PATH,
            'static_path' => STATIC_PATH,
            'visitor_ip' => $_SERVER['REMOTE_ADDR'],
            'title' => 'Sign In',
            'message' => "Sign in failed - invalid credentials",
            'message_type' => 'error'
        ]);
})->setName('login');