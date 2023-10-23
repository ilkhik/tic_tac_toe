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
    
    public function getGameStatusForUser(int $userId)
    {
        $user = $this->userModel->first($userId);
        $game = $this->gameModel->where("
                    status <> '" . Game::STATUS_GAME_OVER . "'
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
                    $game->status == Game::STATUS_CROSS_MOVE && $game->cross == $user->id ||
                    $game->status == Game::STATUS_ZERO_MOVE && $game->zero == $user->id,
            'your_sign' => ($game->cross == $user->id) ? 'cross' : 'zero'
        ];
        return $status;
    }
    
    public function move(int $userId, int $ceil)
    {
        $game = $this->gameModel->where("
                    status <> '" . Game::STATUS_GAME_OVER . "'
                    and
                    (cross = {$userId} or zero = {$userId})
                ")
                ->orderBy('id', 'desc')
                ->first();
                    
        if (    $game->status === Game::STATUS_CROSS_MOVE && $game->cross !== $userId ||
                $game->status === Game::STATUS_ZERO_MOVE && $game->zero !== $userId
            ) {
            throw new \InvalidArgumentException('Сейчас не ваш ход');
        }
        
        $board = json_decode($game->board);
        if ($board[$ceil] !== 0) {
            throw new \InvalidArgumentException('Клетка занята');
        }
        $currentSignCode = ($game->status === Game::STATUS_CROSS_MOVE) ? 1 : 0;
        $board[$ceil] = $currentSignCode;
        if ($this->checkWinner($currentSignCode, $board)) {
            $game->winner = $userId;
            $game->status = Game::STATUS_GAME_OVER;
        } else if ($this->checkFull($board)) {
            $game->status = Game::STATUS_GAME_OVER;
        } else {
            $game->status = ($game->status === Game::STATUS_CROSS_MOVE) ? 
                    Game::STATUS_ZERO_MOVE : 
                    Game::STATUS_CROSS_MOVE;
        }
        $game->board = json_encode($board);
        $this->gameModel->save($game);
        
        $status = [
            'status' => $game->status,
            'board' => $board,
            'your_turn' => 
                    $game->status == Game::STATUS_CROSS_MOVE && $game->cross == $userId ||
                    $game->status == Game::STATUS_ZERO_MOVE && $game->zero == $userId,
            'your_sign' => ($game->cross == $userId) ? 'cross' : 'zero'
        ];
        return $status;
    }
    
    private function checkWinner(int $signCode, array $board): bool
    {
        $winArray = [
            [0,1,2],
            [3,4,5],
            [6,7,8],
            [0,3,6],
            [1,4,7],
            [2,5,8],
            [0,4,8],
            [6,4,2],
        ];
        foreach ($winArray as $win) {
            $match = 0;
            foreach ($win as $i) {
                if ($board[$i] === $signCode) {
                    $match++;
                }
            }
            if ($match === 3) {
                return true;
            }
        }
        return false;
    }
    
    private function checkFull(array $board): bool
    {
        $count = 0;
        foreach ($board as $ceil) {
            if ($ceil !== 0) {
                $count++;
            }
        }
        return $count === 9;
    }
}
