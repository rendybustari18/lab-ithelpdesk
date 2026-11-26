<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout', ['filter' => 'auth']);
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Dashboard::index');
    $routes->get('profile', 'Profile::edit');
    $routes->post('profile', 'Profile::save');
    $routes->get('profile/password', 'Profile::password');
    $routes->post('profile/password', 'Profile::changePassword');
    $routes->get('tickets', 'Tickets::index');
    $routes->get('tickets/new', 'Tickets::new');
    $routes->post('tickets', 'Tickets::create');
    $routes->get('tickets/(:num)', 'Tickets::show/$1');
    $routes->get('tickets/(:num)/edit', 'Tickets::edit/$1');
    $routes->post('tickets/(:num)/edit', 'Tickets::update/$1');
    $routes->post('tickets/(:num)/delete', 'Tickets::delete/$1');
    $routes->post('tickets/(:num)/status', 'Tickets::status/$1');
    $routes->post('tickets/(:num)/assignment', 'Tickets::assign/$1');
    $routes->post('tickets/(:num)/comments', 'Tickets::comment/$1');
    $routes->post('tickets/(:num)/comments/(:num)/edit', 'Tickets::editComment/$1/$2');
    $routes->post('tickets/(:num)/comments/(:num)/delete', 'Tickets::deleteComment/$1/$2');
    $routes->post('tickets/(:num)/attachments', 'Tickets::upload/$1');
    $routes->get('attachments/(:num)', 'Tickets::download/$1');
    $routes->post('attachments/(:num)/delete', 'Tickets::deleteAttachment/$1');
    $routes->get('tickets/(:num)/history', 'Tickets::history/$1');
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/export.csv', 'Reports::export');
    $routes->group('admin', ['filter' => 'role:Administrator'], static function (RouteCollection $routes): void {
        $routes->get('users', 'Admin::users');
        $routes->get('users/new', 'Admin::newUser');
        $routes->post('users', 'Admin::createUser');
        $routes->get('users/(:num)', 'Admin::showUser/$1');
        $routes->get('users/(:num)/edit', 'Admin::editUser/$1');
        $routes->post('users/(:num)/edit', 'Admin::updateUser/$1');
        $routes->post('users/(:num)/reset-password', 'Admin::resetPassword/$1');
        $routes->post('users/(:num)/status', 'Admin::toggleUser/$1');
        $routes->get('categories', 'Admin::categories');
        $routes->post('categories', 'Admin::createCategory');
        $routes->post('categories/(:num)', 'Admin::updateCategory/$1');
        $routes->post('categories/(:num)/status', 'Admin::toggleCategory/$1');
    });
});
