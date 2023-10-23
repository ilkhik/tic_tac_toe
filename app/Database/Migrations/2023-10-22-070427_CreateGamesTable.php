<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGamesTable extends Migration
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
            'cross' => [
                'type' => 'bigint',
                'constraint' => 5,
                'unsigned' => true,
                'null' => true
            ],
            'zero' => [
                'type' => 'bigint',
                'constraint' => 5,
                'unsigned' => true,
                'null' => true
            ],
            'winner' => [
                'type' => 'bigint',
                'constraint' => 5,
                'unsigned' => true,
                'null' => true
            ],
            'board' => [
                'type' => 'varchar',
                'constraint' => '30',
            ],
            'status' => [
                'type' => 'varchar',
                'constraint' => '15',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('cross', 'users', 'id');
        $this->forge->addForeignKey('zero', 'users', 'id');
        $this->forge->addForeignKey('winner', 'users', 'id');
        $this->forge->createTable('games');
    }

    public function down()
    {
        $this->forge->dropTable('games');
    }
}
