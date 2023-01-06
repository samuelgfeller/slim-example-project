<?php

class DbChange122662720063a0b28031205 extends Phinx\Migration\AbstractMigration
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
