<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/signup', 'Signup::Signup');
$routes->post('/signup', 'Signup::Signup');
$routes->get('/login', 'Login::Login');
$routes->post('/login', 'Login::Login');
$routes->get('/logout', 'Logout::Logout');
$routes->post('/logout', 'Logout::Logout');
$routes->get('/dashboard', 'Home::Dashboard');

$routes->post('/update-user', 'UpdateUser::updateUser');
$routes->get('/delete-user/(:num)/(:any)', 'DeleteUser::deleteUser/$1/$2');
$routes->delete('/delete-user/(:num)/(:any)', 'DeleteUser::deleteUser/$1/$2');
$routes->get('/download-users', 'DownloadCsv::downloadCSV');

$routes->get('/uploadCsv', 'UploadCsv::uploadCsv'); 
$routes->post('/uploadCsv', 'UploadCsv::uploadCsv'); 