<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Project;
use App\Models\Transaction;

class CompanyTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::all();
        $projects = Project::all();

        if ($companies->isEmpty() || $projects->isEmpty()) {
            $this->command->info('Please seed companies and projects first.');
            return;
        }

        // Create transactions for each company
        foreach ($companies as $company) {
            // Get random project
            $project = $projects->random();

            // Create 2-4 transactions per company
            $transactionCount = rand(2, 4);

            for ($i = 0; $i < $transactionCount; $i++) {
                $isPaid = rand(0, 1) === 1;
                $isActive = rand(0, 3) > 0; // 75% active, 25% history

                $transactionDate = now()->subMonths(rand(1, 12));
                $contractStartDate = $transactionDate->copy()->subDays(rand(5, 30));

                Transaction::create([
                    'project_id' => $project->id,
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'person_name' => $company->contact_person,
                    'transaction_date' => $transactionDate,
                    'max_overdue_date' => $transactionDate->copy()->addDays(30),
                    'amount' => rand(500, 50000) / 10, // $50 to $5000
                    'payment_status' => $isPaid ? 'paid' : ['unpaid', 'late', 'overdue', 'notified'][rand(0, 3)],
                    'transaction_type' => ['one-time', 'monthly'][rand(0, 1)],
                    'contract_start_date' => $contractStartDate,
                    'contract_end_date' => $contractStartDate->copy()->addMonths(rand(3, 24)),
                    'is_active' => $isActive
                ]);
            }
        }

        $this->command->info('Company transactions seeded successfully!');
    }
}
