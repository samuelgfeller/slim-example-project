<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{

    /**
     * Retrieve the dependencies for this seeder.
     * The seeders returned by this function will be executed before this one.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            'AdminUserSeeder',
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
                'id' => 2,
                'first_name' => 'Manager',
                'surname' => 'Advisor',
                'user_role_id' => 2,
                'status' => 'active',
                'email' => 'managing@advisor.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],
            [
                'id' => 3,
                'first_name' => 'Advisor',
                'surname' => 'User',
                'user_role_id' => 3,
                'status' => 'active',
                'email' => 'advisor@user.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],
            [
                'id' => 4,
                'first_name' => 'Newcomer',
                'surname' => 'User',
                'user_role_id' => 4,
                'status' => 'active',
                'email' => 'newcomer@user.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],

        ];

        $table = $this->table('user');
        $table->insert($userData)->saveData();
    }
}
