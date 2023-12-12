<?php

class DbChange861211597657836b3e7787 extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('client', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Advisors help and consult clients',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('vigilance_level', 'enum', [
                'null' => true,
                'default' => null,
                'limit' => 6,
                'values' => ['low', 'medium', 'high'],
                'after' => 'client_message',
            ])
            ->save();
    }
}
