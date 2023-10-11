<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DbChange12825741316526ba477e6aa extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('authentication_log', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 254,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('ip_address', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 45,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('is_success', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'ip_address',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'curdate()',
                'after' => 'is_success',
            ])
            ->addIndex(['user_id'], [
                'name' => 'authentication_log_user_id_fk',
                'unique' => false,
            ])
            ->create();
        $this->table('email_log', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('from_email', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 254,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('to_email', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 254,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'from_email',
            ])
            ->addColumn('other_recipient', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 1000,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'to_email',
            ])
            ->addColumn('subject', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 998,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'other_recipient',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'curdate()',
                'after' => 'subject',
            ])
            ->addIndex(['user_id'], [
                'name' => 'email_log_user_id_fk',
                'unique' => false,
            ])
            ->create();
        $this->table('user_request')->drop()->save();
    }
}
