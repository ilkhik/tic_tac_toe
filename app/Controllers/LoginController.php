<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class LoginController extends BaseController
{
    use ResponseTrait;
    
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
        $onlineUsersCount = $userModel->builder()
                ->where('is_online', true)
                ->where('last_online >', Time::now()->subMinutes(5))
                ->countAllResults();
        if ($onlineUsersCount >= 2) {
            return $this->respond([
                'message' => 'Достигнуто максимальное количество игроков. Зайдите позже.'
            ], 400);
        }
        $user->is_online = true;
        $user->last_online = Time::now();
        $userModel->save($user);
        
        $jwtService = new \Services\JwtService();
        $tokens = $jwtService->generateJwtForUser($user);
        
        return $this->respond($tokens);
    }
}
