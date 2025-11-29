<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $contract;

    public function __construct(Transaction $transaction, Contract $contract)
    {
        $this->transaction = $transaction;
        $this->contract = $contract;
    }

    public function build()
    {
        $subject = "Payment Reminder - Invoice {$this->transaction->invoice_number}";

        return $this->subject($subject)
                    ->view('emails.payment-notification')
                    ->with([
                        'invoiceNumber' => $this->transaction->invoice_number,
                        'amount' => $this->transaction->amount,
                        'dueDate' => $this->transaction->due_date->format('Y-m-d'),
                        'partnerCompany' => $this->contract->partnerCompany->name,
                        'ownCompany' => $this->contract->ownCompany->name,
                        'product' => $this->contract->product->name,
                    ]);
    }
}
