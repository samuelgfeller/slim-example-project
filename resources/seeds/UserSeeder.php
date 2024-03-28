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
        $userRows = [
            [
                'id' => 2,
                'first_name' => 'Managing-advisor',
                'surname' => 'Surname',
                'user_role_id' => 2,
                'status' => 'active',
                'language' => 'en_US',
                'email' => 'managing-advisor@user.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],
            [
                'id' => 3,
                'first_name' => 'Advisor',
                'surname' => 'Surname',
                'user_role_id' => 3,
                'status' => 'active',
                'language' => 'de_CH',
                'email' => 'advisor@user.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],
            [
                'id' => 4,
                'first_name' => 'Newcomer',
                'surname' => 'Surname',
                'user_role_id' => 4,
                'status' => 'active',
                'language' => 'fr_CH',
                'email' => 'newcomer@user.com',
                'password_hash' => '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', // password: 12345678
            ],

        ];

        $table = $this->table('user');
        $table->insert($userRows)->saveData();
    }
}
