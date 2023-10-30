<?php

class DbChange3897597653fd2bba05de extends Phinx\Migration\AbstractMigration
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
                'limit' => 13,
                'values' => ['low', 'medium', 'high'],
                'after' => 'client_message',
            ])
            ->save();
        $this->table('permission')->drop()->save();
        $this->table('user_request')->drop()->save();
        $this->table('user_role_to_permission')->drop()->save();
    }
}
