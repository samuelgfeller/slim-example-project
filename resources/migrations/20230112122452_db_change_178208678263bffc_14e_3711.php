<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DbChange178208678263bffc14e3711 extends Phinx\Migration\AbstractMigration
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
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'user_id',
            ])
            ->addColumn('message', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 1000,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'client_id',
            ])
            ->addColumn('is_main', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'Bool if it\'s the client\'s main note',
                'after' => 'message',
            ])
            ->addColumn('hidden', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'is_main',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'hidden',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'updated_at',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'FK__user',
                'unique' => false,
            ])
            ->addIndex(['client_id'], [
                'name' => 'FK_note_client',
                'unique' => false,
            ])
            ->create();
        $this->table('user_filter_setting', [
                'id' => false,
                'primary_key' => ['user_id', 'filter_id', 'module'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('filter_id', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'user_id',
            ])
            ->addColumn('module', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'filter_id',
            ])
            ->create();
        $this->table('user_role', [
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
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 30,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('hierarchy', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'name',
            ])
            ->create();
        $this->table('client', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Advisors help and consult clients',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('first_name', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('last_name', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'first_name',
            ])
            ->addColumn('birthdate', 'date', [
                'null' => true,
                'default' => null,
                'after' => 'last_name',
            ])
            ->addColumn('location', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'birthdate',
            ])
            ->addColumn('phone', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 20,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'location',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 254,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'phone',
            ])
            ->addColumn('sex', 'enum', [
                'null' => true,
                'default' => null,
                'limit' => 1,
                'values' => ['M', 'F', 'O'],
                'after' => 'email',
            ])
            ->addColumn('client_message', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 1000,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'Message that client submitted via webform',
                'after' => 'sex',
            ])
            ->addColumn('vigilance_level', 'enum', [
                'null' => true,
                'default' => null,
                'limit' => 13,
                'values' => ['moderate', 'caution', 'extra_caution'],
                'after' => 'client_message',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'vigilance_level',
            ])
            ->addColumn('client_status_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'user_id',
            ])
            ->addColumn('assigned_at', 'datetime', [
                'null' => true,
                'default' => null,
                'comment' => 'date at which user_id was set',
                'after' => 'client_status_id',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'assigned_at',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'updated_at',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'FK_client_user',
                'unique' => false,
            ])
            ->addIndex(['client_status_id'], [
                'name' => 'FK_client_status',
                'unique' => false,
            ])
            ->create();
        $this->table('client_status', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Client status',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'default' => '0',
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('deleted_at', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'name',
            ])
            ->create();
        $this->table('permission', [
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
                'identity' => 'enable',
            ])
            ->addColumn('action', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('module', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'module where action takes place',
                'after' => 'action',
            ])
            ->create();
        $this->table('user', [
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
                'identity' => 'enable',
            ])
            ->addColumn('first_name', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('surname', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'first_name',
            ])
            ->addColumn('user_role_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'surname',
            ])
            ->addColumn('status', 'enum', [
                'null' => true,
                'default' => 'unverified',
                'limit' => 10,
                'values' => ['active', 'locked', 'unverified', 'suspended'],
                'after' => 'user_role_id',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 254,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('password_hash', 'string', [
                'null' => false,
                'limit' => 300,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'password_hash',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'updated_at',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addIndex(['user_role_id'], [
                'name' => 'FK_user_user_role',
                'unique' => false,
            ])
            ->create();
        $this->table('user_activity', [
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
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('action', 'enum', [
                'null' => false,
                'limit' => 7,
                'values' => ['created', 'updated', 'deleted', 'read'],
                'after' => 'user_id',
            ])
            ->addColumn('table', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'action',
            ])
            ->addColumn('row_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'table',
            ])
            ->addColumn('data', 'text', [
                'null' => true,
                'default' => null,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'row_id',
            ])
            ->addColumn('datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'data',
            ])
            ->addColumn('ip_address', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'datetime',
            ])
            ->addColumn('user_agent', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'ip_address',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_activity_user_id_fk',
                'unique' => false,
            ])
            ->create();
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
                'identity' => 'enable',
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
        $this->table('user_role_to_permission', [
                'id' => false,
                'primary_key' => ['user_role_id', 'permission_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_role_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('permission_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'user_role_id',
            ])
            ->addIndex(['permission_id'], [
                'name' => 'user_role_to_permission_permission_null_fk',
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
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 300,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('expires_at', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'after' => 'token',
            ])
            ->addColumn('used_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'expires_at',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'used_at',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'FK__user_table',
                'unique' => false,
            ])
            ->create();
    }
}
