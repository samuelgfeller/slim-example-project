<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{

    /**
     * Dependencies for this seeder.
     * Seeders returned by this function are executed before this one is.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            'UserRoleSeeder',
        ];
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $userData = [
            [
                'first_name' => 'Admin',
                'surname' => null,
                'user_role_id' => 1,
                'status' => 'active',
                'email' => 'admin@admin.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],
        ];

        $table = $this->table('user');
        $table->truncate();
        $table->insert($userData)->saveData();
    }
}
