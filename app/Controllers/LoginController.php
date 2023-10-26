<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use Services\GameService;
use Services\JwtService;
use Services\UserService;

class LoginController extends BaseController
{
    use ResponseTrait;
    
    private GameService $gameService;
    private UserService $userService;
    private UserModel $userModel;

    public function __construct()
    {
        $this->gameService = new GameService();
        $this->userService = new UserService();
        $this->userModel = new UserModel();
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
        
        $this->gameService->startNewGame($user);
        
        $jwtService = new JwtService();
        $tokens = $jwtService->generateJwtForUser($user);
        
        return $this->respond($tokens);
    }
    
    public function logout(): ResponseInterface
    {
        $user = $this->userModel->find($this->request->auth->id);
        $user->is_online = false;
        $user->last_online = Time::now();
        $this->userModel->save($user);
        
        $db = Database::connect();
        $db->table('refresh_tokens')
                ->where('token', $this->request->getJSON()->refresh)
                ->delete();
        
        return $this->respond([
            'message' => 'ok'
        ]);
    }
}
