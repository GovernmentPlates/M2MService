<?php

/*
 * Dependencies file
 * Adapted from lab session/work
 * Author: Clinton Ingrams, Dominic Hollis, Artem Bobrov
 * Email: <cfi@dmu.ac.uk> <p2533140@my365.dmu.ac.uk> <p2547788@my365.dmu.ac.uk>
 *
 * @author CF Ingrams
 * @author D Hollis
 * @author A Bobrov
 */

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'],
        $container['settings']['view']['twig'],
        [
            'debug' => true // This line should enable debug mode
        ]
    );

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;

};

$container['SMSWrapper'] = function () {
    $SMSProcessor_wrapper = new \SMS\SMSProcessor();
    return $SMSProcessor_wrapper;
};

$container['DBWrapper'] = function () {
    $DBInterface_wrapper = new \DB\DBInterface();
    return $DBInterface_wrapper;
};

$container['ValidatorWrapper'] = function () {
    $ValidatorProcessor_wrapper = new \Validator\ValidatorProcessor();
    return $ValidatorProcessor_wrapper;
};

$container['UserWrapper'] = function () {
    $UserProcessor_wrapper = new \User\UserProcessor();
    return $UserProcessor_wrapper;
};

$container['SessionWrapper'] = function () {
    $SessionProcess_wrapper = new \Session\SessionProcessor();
    return $SessionProcess_wrapper;
};

$container['TwoFactorWrapper'] = function () {
    $TwoFactorProcessor_wrapper = new \TwoFactorAuth\TwoFactorProcessor();
    return $TwoFactorProcessor_wrapper;
};