<?php

use App\Controllers\GameController;
use App\Controllers\LoginController;
use App\Controllers\SignUpController;
use App\Controllers\WebController;
use App\Filters\JwtFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// WEB routes
$routes->get('/', [WebController::class, 'index']);
$routes->get('/login', [WebController::class, 'login']);

// API routes
$routes->group('api', static function ($routes) {
    $routes->post(
            'login',
            [LoginController::class, 'login']
    );
    
    $routes->post(
            'sign_up',
            [SignUpController::class, 'index']
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
    
    $routes->post(
            'game/move',
            [GameController::class, 'move'],
            ['filter' => JwtFilter::class]
    );
    
});

