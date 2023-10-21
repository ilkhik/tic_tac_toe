<?php

use App\Controllers\LoginController;
use App\Filters\JwtFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');
$routes->group('/api', function ($routes) {
    $routes->get('login',
            [LoginController::class, 'login']
    );
    $routes->get('test',
            [LoginController::class, 'test'],
            ['filter' => JwtFilter::class]
    );
});

