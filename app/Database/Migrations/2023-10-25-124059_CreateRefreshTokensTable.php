<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefreshTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'token' => [
                'type' => 'varchar',
                'constraint' => '500'
            ],
            'expires' => [
                'type' => 'datetime'
            ],
        ]);
        $this->forge->addKey('token', true);
        $this->forge->createTable('refresh_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('refresh_tokens');
    }
}
