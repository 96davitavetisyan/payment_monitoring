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
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($contract);

            // Check if invoice already exists for this month
            $exists = Transaction::where('contract_id', $contract->id)
                ->where('invoice_number', $invoiceNumber)
                ->exists();

            if ($exists) {
                $this->warn("Invoice {$invoiceNumber} already exists for contract {$contract->id}");
                $skipped++;
                continue;
            }

            try {
                Transaction::create([
                    'contract_id' => $contract->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30), // 30 days payment term
                    'amount' => $contract->payment_amount,
                    'payment_status' => 'pending',
                ]);

                $this->info("Generated invoice {$invoiceNumber} for contract {$contract->id}");
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

    private function generateInvoiceNumber($contract)
    {
        $date = now()->format('Ym');
        $contractId = str_pad($contract->id, 4, '0', STR_PAD_LEFT);
        return "INV-{$date}-{$contractId}";
    }
}
