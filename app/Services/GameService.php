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
                ->where('status <>', Game::STATUS_GAME_OVER)
                ->orderBy('id', 'desc')
                ->first();
        if (!$game) {
            $game = new Game();
            $game->cross = $user->id;
            $game->status = Game::STATUS_WAITING;
            $board = array_fill(0, 9, 0);
            $game->board = json_encode($board);
            $this->gameModel->save($game);
            return;
        }
        
        if ($game->status !== Game::STATUS_WAITING && 
                ($game->cross === $user->id || $game->zero === $user->id)) {
            return;
        }
        if ($game->status === Game::STATUS_WAITING) {
            if ($game->cross === $user->id || $game->zero === $user->id) {
                return;
            }
            
            $enemySign = isset($game->cross) ? 'cross' : 'zero';
            $mySign = isset($game->cross) ? 'zero' : 'cross';
            $enemy = $this->userModel->find($game->$enemySign);
            
            if ( !$this->userService->isOnline($enemy) ) {
                $game->$enemySign = $user->id;
                $this->gameModel->save($game);
                return;
            }
            
            $game->$mySign = $user->id;
            $game->status = Game::STATUS_CROSS_MOVE;
            $this->gameModel->save($game);
            
            //TODO notify the enemy
        }
    }
    
    public function getGameStatusForUser(int $userId)
    {
        $user = $this->userModel->find($userId);
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
                'your_sign' => null,
                'winner' => null
            ];
        }
        $board = json_decode($game->board);
        $status = [
            'status' => $game->status,
            'board' => $board,
            'your_turn' => 
                    $game->status == Game::STATUS_CROSS_MOVE && $game->cross == $user->id ||
                    $game->status == Game::STATUS_ZERO_MOVE && $game->zero == $user->id,
            'your_sign' => ($game->cross == $user->id) ? 'cross' : 'zero',
            'winner' => $game->winner
        ];
        return $status;
    }
    
    public function move(User $user, int $ceil)
    {
        $game = $this->gameModel->where("
                    status not in ( '" . Game::STATUS_GAME_OVER . "', 
                        '" . Game::STATUS_WAITING . "' )
                    and
                    (cross = {$user->id} or zero = {$user->id})
                ")
                ->orderBy('id', 'desc')
                ->first();
                    
        if (!$game) {
            throw new \InvalidArgumentException('Игра не начата');
        }
        if (    $game->status === Game::STATUS_CROSS_MOVE && $game->cross !== $user->id ||
                $game->status === Game::STATUS_ZERO_MOVE && $game->zero !== $user->id
            ) {
            throw new \InvalidArgumentException('Сейчас не ваш ход');
        }
        
        $board = json_decode($game->board);
        if ($board[$ceil] !== 0) {
            throw new \InvalidArgumentException('Клетка занята');
        }
        $currentSignCode = ($game->status === Game::STATUS_CROSS_MOVE) ? 1 : 2;
        $board[$ceil] = $currentSignCode;
        if ($this->checkWinner($currentSignCode, $board)) {
            $game->winner = $user->id;
            $game->status = Game::STATUS_GAME_OVER;
            $user->victories++;
            $this->userModel->save($user);
            $enemyId = ($user->id === $game->cross) ? $game->zero : $game->cross;
            $enemy = $this->userModel->find($enemyId);
            $enemy->defeats++;
            $this->userModel->save($enemy);
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
                    $game->status == Game::STATUS_CROSS_MOVE && $game->cross == $user->id ||
                    $game->status == Game::STATUS_ZERO_MOVE && $game->zero == $user->id,
            'your_sign' => ($game->cross == $user->id) ? 'cross' : 'zero',
            'winner' => $game->winner
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
