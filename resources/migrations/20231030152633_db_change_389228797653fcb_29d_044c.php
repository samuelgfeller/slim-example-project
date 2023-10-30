<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DbChange389228797653fcb29d044c extends Phinx\Migration\AbstractMigration
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
            ->save();
        $this->table('authentication_log', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->save();
        $this->table('email_log', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
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
            ->changeColumn('vigilance_level', 'enum', [
                'null' => true,
                'default' => null,
                'limit' => 6,
                'values' => ['low', 'medium', 'high'],
                'after' => 'client_message',
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
        $this->table('user_request', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => true,
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 254,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('ip_address', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'email',
            ])
            ->addColumn('sent_email', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'ip_address',
            ])
            ->addColumn('is_login', 'enum', [
                'null' => true,
                'default' => null,
                'limit' => 7,
                'values' => ['success', 'failure'],
                'after' => 'sent_email',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'is_login',
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at_index',
                'unique' => false,
            ])
            ->addIndex(['is_login'], [
                'name' => 'request_track_idx_is_login',
                'unique' => false,
            ])
            ->create();
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
