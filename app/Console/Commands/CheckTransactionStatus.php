<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Mail\PaymentReminderMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckTransactionStatus extends Command
{
    protected $signature = 'transactions:check-status';
    protected $description = 'Check transaction statuses and send notifications';

    public function handle()
    {
        $this->info('Starting transaction status check...');

        $today = Carbon::today();
        $notifiedCount = 0;
        $cancelledCount = 0;

        // 1. Check transactions with invoice_date = today and status = pending
        // Send email and change status to notified
        $pendingTransactions = Transaction::with(['contract.partnerCompany'])
            ->whereDate('invoice_date', $today)
            ->where('payment_status', 'pending')
            ->get();

        $this->info("Found {$pendingTransactions->count()} pending transactions for today's invoice date");

        foreach ($pendingTransactions as $transaction) {
            try {
                // Send email notification
                $email = $transaction->contract->partnerCompany->contact_email ?? null;

                if ($email) {
                    Mail::to($email)->send(new PaymentReminderMail($transaction));
                    $this->info("Email sent to {$email} for transaction #{$transaction->id}");
                }

                // Update status to notified
                $transaction->update(['payment_status' => 'notified']);
                $notifiedCount++;

                $this->info("Transaction #{$transaction->id} marked as notified");
            } catch (\Exception $e) {
                $this->error("Failed to process transaction #{$transaction->id}: " . $e->getMessage());
            }
        }

        // 2. Check transactions with due_date = today and status != paid
        // Change status to cancelled
        $overdueTransactions = Transaction::whereDate('due_date', $today)
            ->whereNotIn('payment_status', ['paid', 'cancelled'])
            ->get();

        $this->info("Found {$overdueTransactions->count()} overdue transactions for today's due date");

        foreach ($overdueTransactions as $transaction) {
            try {
                $transaction->update(['payment_status' => 'cancelled']);
                $cancelledCount++;
                $this->info("Transaction #{$transaction->id} marked as cancelled");
            } catch (\Exception $e) {
                $this->error("Failed to cancel transaction #{$transaction->id}: " . $e->getMessage());
            }
        }

        $this->info("\nTransaction status check complete!");
        $this->info("Notified: {$notifiedCount}");
        $this->info("Cancelled: {$cancelledCount}");

        return 0;
    }
}
