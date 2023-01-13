<?php


use Phinx\Seed\AbstractSeed;

class NoteSeeder extends AbstractSeed
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
            'ClientSeeder',
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
        $now = new DateTime();
        $oneDayAgo = $now->sub(new \DateInterval('P01D'))->format('Y-m-d H:i:s');
        $oneWeekAgo = $now->sub(new \DateInterval('P07D'))->format('Y-m-d H:i:s');

        $data = [
            // 1 Gary Preble 2  I have been struggling with addiction to drugs for a long time and I need help to overcome it.
            [
                'id' => 1,
                'user_id' => 2,
                'client_id' => 1,
                'message' => 'Struggling with addiction to drugs.',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'client_id' => 1,
                'message' => 'Spoke with Gary today about his struggles with addiction. He expressed feelings ' .
                    'of hopelessness and a lack of motivation to seek help. Reminded him of the resources available ' .
                    'to him, such as counseling and support groups. Encouraged him to reach out for help and to ' .
                    'stay strong in his recovery journey. Scheduled a follow-up call for next week to check in and ' .
                    'provide support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],

            // 2 Royce Obanion 1 I have been looking for a job for months now and I can't seem to find any stable employment. I am running out of options and I am in need of assistance to find a job.
            [
                'id' => 3,
                'user_id' => 1,
                'client_id' => 2,
                'message' => 'Difficulty finding and maintaining employment',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'client_id' => 2,
                'message' => 'Had a call with Royce today regarding his ongoing job search. He is feeling ' .
                    'discouraged due to a lack of job opportunities and long periods of unemployment. Discussed the ' .
                    'importance of tailoring his resume and cover letter to specific job openings, utilizing ' .
                    'networking opportunities, and considering alternative job options. Provided resources such as ' .
                    'job search websites, workshops, and career counseling services. Scheduled a follow-up call in ' .
                    'two weeks to discuss progress and provide further support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'client_id' => 2,
                'message' => 'Follow-up call with Royce today to check in on his job search progress. Discussed ' .
                    'any new job leads and any challenges he has encountered. Provided additional resources such ' .
                    'as job fairs, and suggested to focus on online presence on LinkedIn and other professional ' .
                    'networks. Encouraged him to not get discouraged and to keep pushing on the job search. ' .
                    'Scheduled another follow-up call in two weeks to continue providing support and guidance.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 6,
                'user_id' => 1,
                'client_id' => 2,
                'message' => 'Royce has called again and happily announced that he has signed a contract for a great ' .
                    'job with lots of opportunities.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ],

            // 3 Anita Gonzalez 2
            [
                'id' => 7,
                'user_id' => 2,
                'client_id' => 3,
                'message' => 'Anita is a single mother of two children. She is struggling to make ends meet and ' .
                    'is behind on rent and utility payments. She is afraid she will lose her home.',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 8,
                'user_id' => 2,
                'client_id' => 3,
                'message' => 'Had a call with Anita today regarding her financial situation. She is behind on ' .
                    'rent and utility payments and is afraid she will lose her home. Discussed the importance of ' .
                    'budgeting and managing her finances. Provided resources such as financial counseling services, ' .
                    'food banks, and utility assistance programs. Scheduled a follow-up call in a week to ' .
                    'discuss progress and provide further support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneDayAgo,
            ],

            // 4 John Jackson 3
            [
                'id' => 9,
                'user_id' => 3,
                'client_id' => 4,
                'message' => 'John is a veteran who is dealing with PTSD. He is experiencing flashbacks, ' .
                    'nightmares, and is having a hard time moving on.',

                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 10,
                'user_id' => 3,
                'client_id' => 4,
                'message' => 'Had a call with John today regarding his PTSD. Discussed the importance of seeking ' .
                    'professional help, such as therapy and counseling, to address these symptoms. Provided ' .
                    'resources such as VA facilities and veterans support groups. Reminded them of the benefits ' .
                    'available to them as a veteran and encouraged them to utilize these resources to aid in his ' .
                    'recovery. Scheduled a follow-up call in two weeks to check in on his progress and provide ' .
                    'further support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 11,
                'user_id' => 3,
                'client_id' => 4,
                'message' => 'Follow-up call with John today regarding his PTSD symptoms. During our conversation, ' .
                    'he disclosed that he has been having thoughts of suicide. I expressed my concern and emphasized ' .
                    'the importance of seeking immediate help. I provided him with the National Suicide Prevention ' .
                    'Lifeline and encourage him to call if he is feeling suicidal. I also provided ' .
                    'him with additional resources such as crisis hotlines and emergency services. I emphasized the ' .
                    'importance of seeking professional help and reminded him that he is not alone in this and there ' .
                    'are people who care about him and want to help. I scheduled another follow-up call in two days ' .
                    'and urged him to reach out to me or the provided resources if he needs any immediate help.',
                'is_main' => 0,
                'hidden' => 1,
                'created_at' => $oneDayAgo,
            ],
            // 5 Chara Joseph 4 I am dealing with the trauma of an event that happened to me. I need help to cope with it.
            [
                'id' => 12,
                'user_id' => 4,
                'client_id' => 5,
                'message' => 'Chara is dealing with the trauma of a past event, a car accident where she lost her ' .
                    'loved one. She is struggling to cope with the emotional and psychological impact of the event .',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 13,
                'user_id' => 4,
                'client_id' => 5,
                'message' => 'Had a call with Chara today regarding her trauma. Discussed the importance of seeking ' .
                    'professional help, such as therapy and counseling, to address these symptoms. Provided ' .
                    'resources such as crisis hotlines and emergency services. Reminded her that she is not alone ' .
                    'in this and there are people who care about her and want to help. Scheduled a follow-up call ' .
                    'in two weeks to check in on her progress and provide further support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneDayAgo,
            ],

            // 6 Annie Trujillo 4
            // Next entry is about Annies difficulty finding and accessing community resources and support.
            [
                'id' => 14,
                'user_id' => 4,
                'client_id' => 6,
                'message' => 'Annie is struggling to find and access community resources and support for her mental ' .
                    'health and well-being. She is experiencing difficulty navigating the complex systems and ' .
                    'bureaucracy involved in accessing these resources. She is feeling isolated and unsupported, ' .
                    'and is in need of assistance in finding the appropriate resources and support for her needs.',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 15,
                'user_id' => 4,
                'client_id' => 6,
                'message' => 'During our call today, we found a support group that was a good fit for her needs. ' .
                    'She felt more hopeful and we scheduled a follow up call in a week.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],
            [
                'id' => 16,
                'user_id' => 3,
                'client_id' => 6,
                'message' => 'Had a follow-up call with Annie today and she was very enthusiastic about the support ' .
                    'group we found for her. She shared that the group has been a great source of support and ' .
                    'validation for her experiences. She also mentioned that she has formed connections with other ' .
                    'group members and it has helped her to feel less alone. I encouraged her to continue to attend ' .
                    'the group and to make use of the resources provided by the facilitators. We scheduled another ' .
                    'follow-up call in a month to check on her progress and to see how she is doing.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneDayAgo,
            ],

            // 7 Frank Walker 3
            [
                'id' => 17,
                'user_id' => 3,
                'client_id' => 7,
                'message' => 'Frank is currently struggling with a chronic autoimmune disorder. He experiences ' .
                    'symptoms such as pain, fatigue, and difficulty performing daily activities. He needs support to ' .
                    'manage the illness, understand his condition, and adapt to a new normal. ',
                'is_main' => 1,
                'hidden' => null, // Always null on main note
            ],
            [
                'id' => 18,
                'user_id' => 3,
                'client_id' => 7,
                'message' => 'Had a call with Frank today regarding his struggles with his chronic autoimmune ' .
                    'disorder. He expressed concerns about managing the symptoms and adjusting to the changes in his ' .
                    'life. Discussed the importance of staying informed about his condition and actively managing it ' .
                    'with his healthcare team. Provided information on support groups and resources such as ' .
                    'financial assistance and home care services. Encouraged him to reach out for emotional and ' .
                    'psychological support. Scheduled a follow-up call in two weeks to check in and provide further ' .
                    'support.',
                'is_main' => 0,
                'hidden' => 0,
                'created_at' => $oneWeekAgo,
            ],
            // 8 Kathryn Eggers 2 I am struggling with intense emotions and impulsivity and need help to improve my mental well-being.

            // Omitting the main note
            [
                'id' => 19,
                'user_id' => 1,
                'client_id' => 8,
                'message' => 'I had a phone call with Kathryn today, who is seeking help with her Borderline ' .
                    'Personality Disorder. She reported experiencing intense mood swings, difficulty maintaining ' .
                    'stable relationships, and difficulty managing her emotions. She has a history of self-harm and ' .
                    'has attempted suicide in the past. During the call, Kathryn expressed a willingness to seek ' .
                    'treatment and work on managing her symptoms. I have referred her to a therapist who specializes ' .
                    'in Borderline Personality Disorder and provided her with information on local support groups ' .
                    'for individuals with BPD. I also discussed safety planning with her and emphasized the ' .
                    'importance of reaching out for help during times of crisis. ' .
                    'She me asked for a list of therapists in her region. I will email it as soon as I have it.',
                'is_main' => 0,
                'hidden' => 1,
                'created_at' => $oneDayAgo,
            ],
            // 9 Estella Escobar 3 I am dealing with domestic violence and abuse. I need help to leave the situation and find a safe place to live.
            [
                'id' => 20,
                'user_id' => 3,
                'client_id' => 9,
                'message' => 'Estella is dealing with domestic violence and abuse.',
                'is_main' => 1,
            ],
            [
                'id' => 21,
                'user_id' => 3,
                'client_id' => 9,
                'message' => 'I had a phone call with Estella today who expressed feeling unsafe and in need urgent ' .
                    'help to leave the situation and find a safe place to live.' . "\n\n" .
                    'During the call, we discussed a plan for her to leave the situation safely and confidentially . ' .
                    'We also talked about the importance of creating a safety plan for her and her children. ' .
                    'I advised her to pack a bag with essentials before leaving the abuser. We also discussed the ' .
                    'importance of notifying trusted friends and family of her situation and the need to reach out ' .
                    'to them for assistance.' . "\n\n" .
                    'We have scheduled a follow - up call for next week to check on her. I have also connected her ' .
                    'with an advocate who specializes in domestic violence cases to provide her with emotional ' .
                    'support and guidance throughout the process.',
                'hidden' => 1,
                'created_at' => $oneDayAgo,
            ],
        ];

        $table = $this->table('note');
        $table->insert($data)->saveData();

        // Insert user_activity
        $userActivityData = [];
        // If user created client, an entry is made in user_activity table
        foreach ($data as $noteData) {
            $userActivityData[] = [
                'user_id' => $noteData['user_id'],
                'action' => 'created',
                'table' => 'note',
                'row_id' => $noteData['id'],
                // json encode relevant keys (all of ClientData->toArrayForDatabase)
                'data' => json_encode(
                    array_intersect_key($noteData, array_flip(['message', 'client_id', 'user_id', 'is_main',])),
                    JSON_THROW_ON_ERROR
                ),
                'datetime' => $noteData['created_at'] ?? $oneWeekAgo,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, ' .
                    'like Gecko) Chrome/108.0.0.0 Safari/537.36 Edg/108.0.1462.54',
            ];
        }
        // Insert user activity
        $table = $this->table('user_activity');
        $table->insert($userActivityData)->saveData();
    }
}
