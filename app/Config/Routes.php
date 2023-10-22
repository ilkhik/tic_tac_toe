<?php

use App\Controllers\GameController;
use App\Controllers\LoginController;
use App\Filters\JwtFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');
$routes->group('api', static function ($routes) {
    $routes->get(
            'login',
            [LoginController::class, 'login']
    );
    
    
    $routes->get(
            'game/status',
            [GameController::class, 'status'],
            ['filter' => JwtFilter::class]
    );
    
    $routes->get(
            'game/user_info',
            [GameController::class, 'userInfo'],
            ['filter' => JwtFilter::class]
    );
    
    
    
});

