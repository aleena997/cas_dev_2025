<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('login', 'AuthController::login');
$routes->get('login/google', 'AuthController::googleLogin');
$routes->get('login/google-callback', 'AuthController::googleCallback');
$routes->get('dashboard', 'AuthController::dashboard');
$routes->get('logout', 'AuthController::logout');

$routes->get('home', 'Dashboard::index');
$routes->post('home/updatePhone', 'Dashboard::updatePhone');

$routes->get('cron/event-reminder', 'CronController::eventReminder');



