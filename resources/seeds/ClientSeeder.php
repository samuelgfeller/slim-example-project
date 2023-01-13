<?php


use Phinx\Seed\AbstractSeed;

class ClientSeeder extends AbstractSeed
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
            'UserSeeder',
            'ClientStatusSeeder',
        ];
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     * @throws JsonException
     */
    public function run(): void
    {
        $oneWeekAgo = (new \DateTime())->sub(new \DateInterval('P08D'))->format('Y-m-d H:i:s');

        $data = [
            [
                'id' => 1,
                'first_name' => 'Gary',
                'last_name' => 'Preble',
                'birthdate' => '2000-02-07',
                'location' => 'Scharans',
                'phone' => '081 384 16 31',
                'email' => 'GaryHPreble@fleckens.com',
                'sex' => 'M',
                'client_message' => 'I have been struggling with addiction to drugs for a long time and I need help' .
                    ' to overcome it.',
                'vigilance_level' => null,
                'user_id' => 2,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 2,
                'first_name' => 'Royce',
                'last_name' => 'Obanion',
                'birthdate' => '1967-08-16',
                'location' => 'Bussnang',
                'phone' => '071 674 25 48',
                'email' => 'RoyceLObanion@fleckens.com',
                'sex' => 'M',
                'client_message' => 'I have been looking for a job for months now and I can\'t seem to find any ' .
                    'stable employment. I am running out of options and I am in need of assistance to find a job.',
                'vigilance_level' => null,
                'user_id' => 1,
                'client_status_id' => 3,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 3,
                'first_name' => 'Anita',
                'last_name' => 'Gonzalez',
                'birthdate' => '1996-01-20',
                'location' => 'Steinhaus',
                'phone' => '027 828 44 91',
                'email' => 'AnitaNGonzalez@rhyta.com',
                'sex' => 'F',
                'client_message' => null,
                'vigilance_level' => null,
                'user_id' => 2,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 4,
                'first_name' => 'John',
                'last_name' => 'Jackson',
                'birthdate' => '1953-12-10',
                'location' => 'Kesswil',
                'phone' => '052 435 34 22',
                'email' => 'JohnJJackson@teleworm.com',
                'sex' => 'M',
                'client_message' => null,
                'vigilance_level' => null,
                'user_id' => 3,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 5,
                'first_name' => 'Chara',
                'last_name' => 'Joseph',
                'birthdate' => '1994-10-11',
                'location' => 'Les Avants',
                'phone' => '024 474 34 63',
                'email' => 'CharaDJoseph@einrot.com',
                'sex' => 'F',
                'client_message' => 'I am dealing with the trauma of an event that happened to me. I need help ' .
                    'to cope with it.',
                'vigilance_level' => null,
                'user_id' => 4,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 6,
                'first_name' => 'Annie',
                'last_name' => 'Trujillo',
                'birthdate' => '1954-09-29',
                'location' => 'Aproz',
                'phone' => '027 879 84 92',
                'email' => 'AnniePTrujillo@rhyta.com',
                'sex' => 'F',
                'client_message' => null,
                'vigilance_level' => null,
                'user_id' => 4,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 7,
                'first_name' => 'Frank',
                'last_name' => 'Walker',
                'birthdate' => '1957-11-05',
                'location' => 'St. Pelagiberg',
                'phone' => '071 385 64 44',
                'email' => 'FrankGWalker@rhyta.com',
                'sex' => 'M',
                'client_message' => null,
                'vigilance_level' => null,
                'user_id' => 3,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 8,
                'first_name' => 'Kathryn',
                'last_name' => 'Eggers',
                'birthdate' => '1953-08-01',
                'location' => 'Wengliswil',
                'phone' => '026 877 92 78',
                'email' => 'KathrynJEggers@armyspy.com',
                'sex' => 'F',
                'client_message' => 'I am struggling with intense emotions and impulsivity and need help to improve' .
                    ' my mental well-being.',
                'vigilance_level' => null,
                'user_id' => 1,
                'client_status_id' => 1,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 9,
                'first_name' => 'Estella',
                'last_name' => 'Escobar',
                'birthdate' => '1982-10-09',
                'location' => 'La Heutte',
                'phone' => '032 267 77 50',
                'email' => 'EstellaTEscobar@jourrapide.com',
                'sex' => 'F',
                'client_message' => 'I am dealing with domestic violence and abuse. I need help to leave the situation' .
                    ' and find a safe place to live.',
                'vigilance_level' => null,
                'user_id' => 3,
                'client_status_id' => 2,
                'assigned_at' => null,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 10,
                'first_name' => 'Erin',
                'last_name' => 'Freeman',
                'birthdate' => '2000-06-09',
                'location' => 'Arnex-sur-Nyon',
                'phone' => '022 376 13 54',
                'email' => 'ErinJFreeman@teleworm.com',
                'sex' => 'M',
                'client_message' => 'I lost a loved one recently and I am having a hard time coping with the loss.',
                'vigilance_level' => null,
                'user_id' => null,
                'client_status_id' => 1,
                'assigned_at' => null,
                // 1 day ago
                'created_at' => (new DateTime())->sub(new \DateInterval('P01D'))->format('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('client');
        $table->insert($data)->saveData();

        // Insert user_activity
        $userActivityData = [];
        // If user created client, an entry is made in user_activity table
        foreach ($data as $clientData) {
            // If client_message is empty, it means that a user created the client entry
            if ($clientData['client_message'] === null) {
                $userActivityData[] = [
                    'user_id' => $clientData['user_id'],
                    'action' => 'created',
                    'table' => 'client',
                    'row_id' => $clientData['id'],
                    // json encode relevant keys (all of ClientData->toArrayForDatabase)
                    'data' => json_encode(
                        array_intersect_key(
                            $clientData,
                            array_flip([
                                'first_name',
                                'last_name',
                                'birthdate',
                                'location',
                                'phone',
                                'email',
                                'sex',
                                'client_message',
                                'vigilance_level',
                                'user_id',
                                'client_status_id'
                            ])
                        ),
                        JSON_THROW_ON_ERROR
                    ),
                    'datetime' => $clientData['created_at'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, ' .
                        'like Gecko) Chrome/108.0.0.0 Safari/537.36 Edg/108.0.1462.54',
                ];
            }
        }
        // Insert user activity
        $table = $this->table('user_activity');
        $table->insert($userActivityData)->saveData();
    }
}
