<?php


use Phinx\Seed\AbstractSeed;

class ClientStatusSeeder extends AbstractSeed
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
        $clientStatusData = [
            ['name' => 'Action pending'],
            ['name' => 'In progress'],
            ['name' => 'In care'],
            ['name' => 'Cannot help'],
        ];

        $table = $this->table('client_status');

        $table->insert($clientStatusData)->saveData();
    }
}
