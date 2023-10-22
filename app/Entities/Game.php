<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int $id
 * @property int $cross
 * @property int $zero
 * @property ?int $winner
 * @property string $turn
 * @property string $board
 * @property string $status
 */
class Game extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id' => 'int',
        'cross' => 'int',
        'zero' => 'int',
        'winner' => '?int',
    ];
    
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CROSS_MOVE = 'cross_move';
    public const STATUS_ZERO_MOVE = 'zero_move';
    public const STATUS_GAME_OVER = 'game_over';
    
    public const TURN_CROSS = 'cross';
    public const TURN_ZERO = 'zero';
}
