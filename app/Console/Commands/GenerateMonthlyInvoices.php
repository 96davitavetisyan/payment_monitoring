<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly';
    protected $description = 'Generate monthly invoices for active contracts';

    public function handle()
    {
        $this->info('Starting monthly invoice generation...');

        $activeContracts = Contract::where('status', 'active')
            ->where('payment_type', 'monthly')
            ->whereDate('contract_start_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('contract_end_date')
                      ->orWhereDate('contract_end_date', '>=', now());
            })
            ->with(['partnerCompany', 'ownCompany', 'product'])
            ->get();

        $this->info("Found {$activeContracts->count()} active monthly contracts");

        $generated = 0;
        $skipped = 0;

        foreach ($activeContracts as $contract) {
            try {
                Transaction::create([
                    'contract_id' => $contract->id,
                    'invoice_number' => $contract->account_number,
                    'invoice_date' => $this->formatPaymentDate($contract->payment_date),
                    'due_date' => $this->formatPaymentDate($contract->payment_finish_date),
                    'amount' => $contract->payment_amount,
                    'payment_status' => 'pending',
                ]);

                $this->info("Generated invoice for contract {$contract->id}");
                $generated++;
            } catch (\Exception $e) {
                $this->error("Failed to generate invoice for contract {$contract->id}: " . $e->getMessage());
            }
        }

        $this->info("\nInvoice generation complete!");
        $this->info("Generated: {$generated}");
        $this->info("Skipped: {$skipped}");

        return 0;
    }

    private function formatPaymentDate($day)
    {
        if (!$day) {
            return '-';
        }
        $today = Carbon::today();
        $date = Carbon::create($today->year, $today->month, $day, 0, 0, 0);
        if ($date->lt($today)) {
            $date->addMonth();
        }

        return $date;
    }
}
