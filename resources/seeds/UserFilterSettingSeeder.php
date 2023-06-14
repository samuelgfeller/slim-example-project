<?php


use Phinx\Seed\AbstractSeed;

class UserFilterSettingSeeder extends AbstractSeed
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
            'UserSeeder',
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
        // Insert dashboard user filter seeder so that the front page is less empty
        $data = [
            [
                'user_id' => 1,
                'filter_id' => 'assigned-to-me-panel',
                'module' => 'dashboard-panel',
            ],
            [
                'user_id' => 1,
                'filter_id' => 'new-notes-panel',
                'module' => 'dashboard-panel',
            ],
            [
                'user_id' => 1,
                'filter_id' => 'recently-assigned-panel',
                'module' => 'dashboard-panel',
            ],
            [
                'user_id' => 1,
                'filter_id' => 'unassigned-panel',
                'module' => 'dashboard-panel',
            ],

        ];

        $table = $this->table('user_filter_setting');
        $table->insert($data)->saveData();
    }
}
