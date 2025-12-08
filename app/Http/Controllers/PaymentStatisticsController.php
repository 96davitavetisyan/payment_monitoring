<?php

namespace App\Http\Controllers;

use App\Models\PartnerCompany;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentStatisticsController extends Controller
{
    public function index()
    {
        $partners = PartnerCompany::with(['contracts.transactions' => function($query) {
            $query->where('payment_status', 'paid');
        }])->get();

        $statistics = [];

        foreach ($partners as $partner) {
            $stats = $this->calculatePartnerStatistics($partner);
            if ($stats['total_payments'] > 0) {
                $statistics[] = $stats;
            }
        }

        // Сортируем по категории (bad первые) и количеству просрочек
        usort($statistics, function($a, $b) {
            if ($a['category'] !== $b['category']) {
                return $a['category'] === 'bad' ? -1 : 1;
            }
            return $b['overdue_count'] - $a['overdue_count'];
        });

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    private function calculatePartnerStatistics($partner)
    {
        $onTime = 0;
        $afterInvoice = 0;
        $overdue = 0;
        $totalPayments = 0;

        foreach ($partner->contracts as $contract) {
            foreach ($contract->transactions as $transaction) {
                if ($transaction->payment_status !== 'paid' || !$transaction->paid_date) {
                    continue;
                }

                $totalPayments++;
                $paidDate = Carbon::parse($transaction->paid_date);
                $invoiceDate = Carbon::parse($transaction->invoice_date);
                $dueDate = Carbon::parse($transaction->due_date);

                if ($paidDate->lte($invoiceDate)) {
                    // Оплачено до или в день invoice_date
                    $onTime++;
                } elseif ($paidDate->lte($dueDate)) {
                    // Оплачено после invoice_date но до или в день due_date
                    $afterInvoice++;
                } else {
                    // Оплачено после due_date (просрочка)
                    $overdue++;
                }
            }
        }

        // Определяем категорию
        $category = 'good';
        if ($overdue >= 2) {
            $category = 'bad';
        }

        return [
            'partner_id' => $partner->id,
            'partner_name' => $partner->name,
            'contact_person' => $partner->contact_person,
            'contact_email' => $partner->contact_email,
            'contact_phone' => $partner->contact_phone,
            'total_payments' => $totalPayments,
            'on_time' => $onTime,
            'after_invoice' => $afterInvoice,
            'overdue_count' => $overdue,
            'category' => $category,
            'on_time_percentage' => $totalPayments > 0 ? round(($onTime / $totalPayments) * 100, 1) : 0,
            'after_invoice_percentage' => $totalPayments > 0 ? round(($afterInvoice / $totalPayments) * 100, 1) : 0,
            'overdue_percentage' => $totalPayments > 0 ? round(($overdue / $totalPayments) * 100, 1) : 0,
        ];
    }
}
