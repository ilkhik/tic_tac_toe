<?php

namespace Services;

use App\Entities\Game;
use App\Entities\User;
use App\Models\GameModel;
use App\Models\UserModel;

class GameService
{
    private GameModel $gameModel;
    private UserModel $userModel;
    private UserService $userService;

    public function __construct()
    {
        $this->gameModel = new GameModel();
        $this->userModel = new UserModel();
        $this->userService = new UserService();
    }
    
    public function passUserToGame(User $user): void
    {
        $game = $this->gameModel
                ->where('status', Game::STATUS_WAITING)
                ->orderBy('id', 'desc')
                ->first();
        if (!$game) {
            $game = new Game();
            $game->cross = $user->id;
            $game->status = Game::STATUS_WAITING;
            $board = [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
            ];
            $game->board = json_encode($board);
            $game->turn = Game::TURN_CROSS;
            $this->gameModel->save($game);
            return;
        }
        
        if ($game->cross && !$game->zero) {
            $enemy = $this->userModel->find($game->cross);
            
            if ( !$this->userService->isOnline($enemy) ) {
                $game->cross = $user->id;
                $this->gameModel->save($game);
                return;
            }
            
            $game->zero = $user->id;
            $game->status = Game::STATUS_CROSS_MOVE;
            $this->gameModel->save($game);
            
            //TODO notify the enemy
        }
    }
    
    public function getGameStatusForUser(User $user)
    {
        $game = $this->gameModel->where("
                    status = '" . Game::STATUS_WAITING . "'
                    and
                    (cross = {$user->id} or zero = {$user->id})
                ")
                ->orderBy('id', 'desc')
                ->first();

        if (!$game) {
            return [
                'status' => null,
                'board' => null,
                'your_turn' => null,
                'your_sign' => null
            ];
        }
        $board = json_decode($game->board);
        $status = [
            'status' => $game->status,
            'board' => $board,
            'your_turn' => 
                    $game->turn == Game::TURN_CROSS && $game->cross == $user->id ||
                    $game->turn == Game::TURN_ZERO && $game->zero == $user->id,
            'your_sign' => ($game->cross == $user->id) ? Game::TURN_CROSS : Game::TURN_ZERO
        ];
        return $status;
    }
}
