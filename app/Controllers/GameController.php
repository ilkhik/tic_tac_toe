<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use Services\GameService;
use Services\UserService;

class GameController extends BaseController
{
    use ResponseTrait;
    
    private UserModel $userModel;
    private GameService $gameService;
    private UserService $userService;


    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->gameService = new GameService();
        $this->userService = new UserService();
    }


    public function status(): ResponseInterface
    {
        return $this->respond($this->gameService->getGameStatusForUser($this->request->auth->id));
    }
    
    public function userInfo(): ResponseInterface
    {
        $userId = $this->request->auth->id;
        $info = $this->userService->getInfo($userId);
        return $this->respond($info);
    }
    
    public function move(): ResponseInterface
    {
        $ceil = $this->request->getJSON()->ceil;
        if ($ceil < 0 || $ceil > 8) {
            return $this->respond([
                'message' => 'Невалидный номер клетки'
            ], 400);
        }
        $user = $this->userModel->find($this->request->auth->id);
        try {
            $gameStatus = $this->gameService->move($user, $ceil);
        } catch (InvalidArgumentException $e) {
            return $this->respond([
                'message' => $e->getMessage()
            ], 400);
        }
        return $this->respond($gameStatus);
    }
    
    public function start(): ResponseInterface
    {
        $user = $this->userModel->find($this->request->auth->id);
        $this->gameService->startNewGame($user);
        $response = $this->gameService->getGameStatusForUser($user->id);
        return $this->respond($response);
    }
}
