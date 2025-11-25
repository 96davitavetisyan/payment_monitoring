<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectsSeeder extends Seeder
{
    public function run()
    {
        $projects = [
            [
                'name' => 'Nazar',
                'start_date' => now()->subMonths(2),
                'responsible_user_id' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Ari Ari',
                'start_date' => now()->subMonths(1),
                'responsible_user_id' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'Tamias',
                'start_date' => now(),
                'responsible_user_id' => 3,
                'status' => 'suspended',
            ],
            [
                'name' => 'Pay Ways',
                'start_date' => now()->subDays(15),
                'responsible_user_id' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Nister',
                'start_date' => now()->subDays(10),
                'responsible_user_id' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
