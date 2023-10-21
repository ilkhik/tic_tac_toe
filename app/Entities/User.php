<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $victories
 * @property int $defeats
 */
class User extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id' => 'int',
        'victories' => 'int',
        'defeats' => 'int',
        'is_online' => 'bool',
    ];
}
