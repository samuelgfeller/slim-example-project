<?php

class DbChange1359795189647891f0c989d extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('note', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'hidden',
            ])
            ->save();
        $this->table('client', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Advisors help and consult clients',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'assigned_at',
            ])
            ->save();
        $this->table('user', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'password_hash',
            ])
            ->save();
        $this->table('user_activity', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->save();
        $this->table('user_verification', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->save();
    }
}
