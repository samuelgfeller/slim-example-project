<?php

class DbChange1316013665647f42494524b extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('user', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('theme', 'enum', [
                'null' => true,
                'default' => 'light',
                'limit' => 5,
                'values' => ['light', 'dark'],
                'after' => 'password_hash',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'theme',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'updated_at',
            ])
            ->changeColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->save();
    }
}
