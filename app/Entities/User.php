<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $victories
 * @property int $defeats
 * @property bool $is_online
 * @property Time $last_online
 */
class User extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at', 'last_online'];
    protected $casts   = [
        'id' => 'int',
        'victories' => 'int',
        'defeats' => 'int',
        'is_online' => 'bool',
    ];
}
