<?php

use App\Controllers\GameController;
use App\Controllers\JwtController;
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
            'logout',
            [LoginController::class, 'logout'],
            ['filter' => JwtFilter::class]
    );
    
    $routes->post(
            'sign_up',
            [SignUpController::class, 'index']
    );
    
    $routes->post(
            'refresh_token',
            [JwtController::class, 'refresh']
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
    
    $routes->post(
            'game/start',
            [GameController::class, 'start'],
            ['filter' => JwtFilter::class]
    );
    
});

