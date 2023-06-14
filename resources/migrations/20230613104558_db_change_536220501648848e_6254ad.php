<?php

class DbChange536220501648848e6254ad extends Phinx\Migration\AbstractMigration
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
            ->addColumn('language', 'enum', [
                'null' => true,
                'default' => 'en_US',
                'limit' => 5,
                'values' => ['en_US', 'de_CH', 'fr_CH'],
                'after' => 'theme',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'language',
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
