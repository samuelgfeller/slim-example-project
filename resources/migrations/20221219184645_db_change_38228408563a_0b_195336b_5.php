<?php

class DbChange38228408563a0b195336b5 extends Phinx\Migration\AbstractMigration
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
            ->removeColumn('column_name')
            ->save();
    }
}
