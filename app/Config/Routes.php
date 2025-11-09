<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - redirect to auth
$routes->get('/', 'Auth::index');

// Authentication routes
$routes->group('auth', function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('/', 'Auth::index');
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::register');
    $routes->get('logout', 'Auth::logout');
    $routes->get('lupas', 'Auth::lupas');
    $routes->post('lupas', 'Auth::lupas');
    $routes->get('resetpassword', 'Auth::resetpassword');
    $routes->get('gantipassword', 'Auth::gantipassword');
    $routes->post('gantipassword', 'Auth::gantipassword');
});

// Admin routes (protected - will need middleware)
$routes->group('admin', function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('dashboard', 'Admin::index');
});

// Accounting routes (protected - will need middleware)
$routes->group('', function ($routes) {
    // Chart of Accounts
    $routes->get('master', 'Master::index');
    $routes->get('master/add', 'Master::add');
    $routes->post('master/save', 'Master::save');
    $routes->get('master/edit/(:num)', 'Master::edit/$1');
    $routes->post('master/update/(:num)', 'Master::update/$1');
    $routes->get('master/delete/(:num)', 'Master::delete/$1');

    // Journal entries
    $routes->get('jp', 'Jp::index');
    $routes->get('jp/add', 'Jp::add');
    $routes->post('jp/save', 'Jp::save');
    $routes->get('jp/detail/(:num)', 'Jp::detail/$1');
    $routes->get('jp/approve/(:num)', 'Jp::approve/$1');

    // General ledger
    $routes->get('buku_besar', 'BukuBesar::index');
    $routes->get('buku_besar/account/(:num)', 'BukuBesar::account/$1');

    // Financial reports
    $routes->get('labarugi', 'Labarugi::index');
    $routes->get('poskeu', 'Poskeu::index');
    $routes->get('per_modal', 'PerModal::index');
});
