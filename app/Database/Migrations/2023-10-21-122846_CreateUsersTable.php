<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'bigint',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'varchar',
                'constraint' => '30',
                'unique' => true
            ],
            'password' => [
                'type' => 'varchar',
                'constraint' => '100'
            ],
            'victories' => [
                'type' => 'integer',
                'unsigned' => true,
                'default' => 0
            ],
            'defeats' => [
                'type' => 'integer',
                'unsigned' => true,
                'default' => 0
            ],
            'is_online' => [
                'type' => 'bool',
                'default' => 0
            ],
            'last_online' => [
                'type' => 'datetime'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
