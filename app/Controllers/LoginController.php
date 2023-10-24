<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Services\GameService;
use Services\JwtService;
use Services\UserService;

class LoginController extends BaseController
{
    use ResponseTrait;
    
    private GameService $gameService;
    private UserService $userService;
    
    public function __construct()
    {
        $this->gameService = new GameService();
        $this->userService = new UserService();
    }
    
    public function login(): ResponseInterface
    {
        $request = $this->request->getJSON();
        $userModel = new UserModel();
        $user = $userModel->where('username', $request->login)->first();
        if (!$user) {
            return $this->respond([
                'message' => 'Неверный логин или пароль'
            ], 400);
        }
        if (!password_verify($request->password, $user->password)) {
            return $this->respond([
                'message' => 'Неверный логин или пароль'
            ], 400);
        }
        $onlineUsersCount = $this->userService->getOnlineUsersCount();
        if ($onlineUsersCount >= 2 && !$this->userService->isOnline($user)) {
            return $this->respond([
                'message' => 'Достигнуто максимальное количество игроков. Зайдите позже.'
            ], 400);
        }
        $user->is_online = true;
        $user->last_online = Time::now();
        $userModel->save($user);
        
        $this->gameService->passUserToGame($user);
        
        $jwtService = new JwtService();
        $tokens = $jwtService->generateJwtForUser($user);
        
        return $this->respond($tokens);
    }
}
