<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use Services\GameService;
use Services\JwtService;

class SignUpController extends BaseController
{
    use ResponseTrait;
    
    private UserModel $userModel;
    private GameService $gameService;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->gameService = new GameService();
    }


    public function index()
    {
        $request = $this->request->getJSON();
        $user = new User();
        $user->username = $request->login;
        $user->password = $request->password;
        $userExists = $this->userModel->where('username', $user->username)->countAllResults() > 0;
        if ($userExists) {
            return $this->respond([
                'message' => 'Имя пользователя занято'
            ], 409);
        }
        $user->password = password_hash($user->password, PASSWORD_BCRYPT);
        $user->is_online = true;
        $user->last_online = Time::now();
        $this->userModel->save($user);
        $user->id = $this->userModel->getInsertID();
        
        $this->gameService->passUserToGame($user);
        
        $jwtService = new JwtService();
        $tokens = $jwtService->generateJwtForUser($user);
        
        return $this->respond($tokens);
    }
}
