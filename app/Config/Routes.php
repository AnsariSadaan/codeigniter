<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/signup', 'Home::Signup');
$routes->get('/userdata', 'Home::Userdata');
$routes->post('/signup', 'Home::Signup');
$routes->get('/login', 'Home::Login');
$routes->post('/login', 'Home::Login');
$routes->get('/dashboard', 'Home::Dashboard');
