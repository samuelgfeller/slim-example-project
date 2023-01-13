<?php


use Phinx\Seed\AbstractSeed;

class UserRoleSeeder extends AbstractSeed
{
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
        $userRoleData = [
            ['id' => 1, 'name' => 'admin', 'hierarchy' => 1],
            ['id' => 2, 'name' => 'managing_advisor', 'hierarchy' => 2],
            ['id' => 3, 'name' => 'advisor', 'hierarchy' => 3],
            ['id' => 4, 'name' => 'newcomer', 'hierarchy' => 4],
        ];

        $table = $this->table('user_role');

        $table->insert($userRoleData)->saveData();
    }
}
