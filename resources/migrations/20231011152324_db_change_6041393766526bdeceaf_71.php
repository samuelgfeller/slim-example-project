<?php

class DbChange6041393766526bdeceaf71 extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('email_log', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'subject',
            ])
            ->save();
    }
}
