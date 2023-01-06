<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DbChange88150668463a096caf1863 extends Phinx\Migration\AbstractMigration
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
            ->addColumn('column_name', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'deleted_at',
            ])
            ->save();
    }
}
