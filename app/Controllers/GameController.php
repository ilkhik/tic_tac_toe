<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Services\GameService;

class GameController extends BaseController
{
    use ResponseTrait;
    
    private UserModel $userModel;
    private GameService $gameService;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->gameService = new GameService();
    }


    public function status(): ResponseInterface
    {
        $user = $this->userModel->first($this->request->auth);
        return $this->respond($this->gameService->getGameStatusForUser($user));
    }
    
    public function userInfo(): ResponseInterface
    {
        $currentUserData = $this->request->auth;
        $user = $this->userModel->find($currentUserData->id);
        
        $response = [
            'id' => $user->id,
            'username' => $user->username,
            'victories' => $user->victories,
            'defeats' => $user->defeats,
        ];
        return $this->respond($response);
    }
}
