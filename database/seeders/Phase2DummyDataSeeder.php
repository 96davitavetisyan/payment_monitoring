<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartnerCompany;
use App\Models\OwnCompany;
use App\Models\Product;
use App\Models\Contract;
use App\Models\Transaction;

class Phase2DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Partner Companies
        $partnerCompanies = [
            [
                'name' => 'Tech Solutions LLC',
                'contact_person' => 'John Smith',
                'contact_email' => 'john@techsolutions.com',
                'contact_phone' => '+374-91-123456',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Innovations CJSC',
                'contact_person' => 'Anna Petrosyan',
                'contact_email' => 'anna@digitalinnovations.am',
                'contact_phone' => '+374-93-234567',
                'is_active' => true,
            ],
            [
                'name' => 'Global Services Inc',
                'contact_person' => 'Michael Johnson',
                'contact_email' => 'michael@globalservices.com',
                'contact_phone' => '+374-94-345678',
                'is_active' => true,
            ],
            [
                'name' => 'Armenian Tech Group',
                'contact_person' => 'Armen Sargsyan',
                'contact_email' => 'armen@armtech.am',
                'contact_phone' => '+374-95-456789',
                'is_active' => true,
            ],
            [
                'name' => 'Yerevan Software Studio',
                'contact_person' => 'Lusine Hakobyan',
                'contact_email' => 'lusine@yerevansoft.am',
                'contact_phone' => '+374-96-567890',
                'is_active' => false,
            ],
        ];

        foreach ($partnerCompanies as $company) {
            PartnerCompany::create($company);
        }

        // Create Own Companies
        $ownCompanies = [
            [
                'name' => 'Payment Pro Armenia',
                'legal_name' => 'Payment Pro Armenia CJSC',
                'tax_id' => '01234567',
                'address' => '1 Alek Manukyan St, Yerevan 0070, Armenia',
                'phone' => '+374-10-123456',
                'email' => 'info@paymentpro.am',
                'is_active' => true,
            ],
            [
                'name' => 'FinTech Solutions AM',
                'legal_name' => 'FinTech Solutions Armenia LLC',
                'tax_id' => '12345678',
                'address' => '5 Teryan St, Yerevan 0001, Armenia',
                'phone' => '+374-10-234567',
                'email' => 'contact@fintechsolutions.am',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Payments LLC',
                'legal_name' => 'Digital Payments Armenia LLC',
                'tax_id' => '23456789',
                'address' => '12 Sayat-Nova Ave, Yerevan 0001, Armenia',
                'phone' => '+374-10-345678',
                'email' => 'hello@digitalpayments.am',
                'is_active' => true,
            ],
        ];

        foreach ($ownCompanies as $company) {
            OwnCompany::create($company);
        }

        // Get all products to use in contracts
        $products = Product::all();

        // Create Contracts
        $contracts = [
            [
                'partner_company_id' => 1,
                'own_company_id' => 1,
                'product_id' => $products->first()->id ?? 1,
                'contract_number' => 'CNT-2025-001',
                'contract_start_date' => '2025-01-01',
                'contract_end_date' => '2025-12-31',
                'payment_type' => 'monthly',
                'payment_amount' => 500000,
                'status' => 'active',
                'notes' => 'Annual contract with monthly payments',
            ],
            [
                'partner_company_id' => 2,
                'own_company_id' => 1,
                'product_id' => $products->skip(1)->first()->id ?? 2,
                'contract_number' => 'CNT-2025-002',
                'contract_start_date' => '2025-02-01',
                'contract_end_date' => '2026-01-31',
                'payment_type' => 'monthly',
                'payment_amount' => 750000,
                'status' => 'active',
                'notes' => 'Premium service package',
            ],
            [
                'partner_company_id' => 3,
                'own_company_id' => 2,
                'product_id' => $products->skip(2)->first()->id ?? 3,
                'contract_number' => 'CNT-2025-003',
                'contract_start_date' => '2025-03-01',
                'contract_end_date' => null,
                'payment_type' => 'one-time',
                'payment_amount' => 2500000,
                'status' => 'active',
                'notes' => 'One-time implementation fee',
            ],
            [
                'partner_company_id' => 4,
                'own_company_id' => 2,
                'product_id' => $products->skip(3)->first()->id ?? 4,
                'contract_number' => 'CNT-2025-004',
                'contract_start_date' => '2025-01-15',
                'contract_end_date' => '2025-07-15',
                'payment_type' => 'monthly',
                'payment_amount' => 350000,
                'status' => 'active',
                'notes' => 'Six-month trial period',
            ],
            [
                'partner_company_id' => 1,
                'own_company_id' => 3,
                'product_id' => $products->last()->id ?? 5,
                'contract_number' => 'CNT-2024-999',
                'contract_start_date' => '2024-06-01',
                'contract_end_date' => '2024-12-31',
                'payment_type' => 'monthly',
                'payment_amount' => 400000,
                'status' => 'completed',
                'notes' => 'Completed contract from 2024',
            ],
        ];

        foreach ($contracts as $contract) {
            Contract::create($contract);
        }

        // Create Transactions for active contracts
        $activeContracts = Contract::where('status', 'active')->get();

        foreach ($activeContracts as $contract) {
            if ($contract->payment_type === 'monthly') {
                // Create 3-4 monthly invoices
                for ($i = 0; $i < 3; $i++) {
                    $invoiceDate = now()->subMonths(2 - $i)->startOfMonth();
                    $dueDate = $invoiceDate->copy()->addDays(15);

                    Transaction::create([
                        'contract_id' => $contract->id,
                        'invoice_number' => 'INV-' . $invoiceDate->format('Ym') . '-' . str_pad($contract->id, 3, '0', STR_PAD_LEFT),
                        'invoice_date' => $invoiceDate->format('Y-m-d'),
                        'due_date' => $dueDate->format('Y-m-d'),
                        'amount' => $contract->payment_amount,
                        'payment_status' => $i === 0 ? 'paid' : ($i === 1 ? 'pending' : 'late'),
                        'paid_date' => $i === 0 ? $invoiceDate->copy()->addDays(5)->format('Y-m-d') : null,
                        'notes' => $i === 0 ? 'Paid on time' : ($i === 1 ? 'Payment pending' : 'Payment overdue'),
                        'notified_at' => $i === 2 ? now() : null,
                    ]);
                }
            } else {
                // One-time payment
                Transaction::create([
                    'contract_id' => $contract->id,
                    'invoice_number' => 'INV-' . now()->format('Ym') . '-' . str_pad($contract->id, 3, '0', STR_PAD_LEFT),
                    'invoice_date' => $contract->contract_start_date,
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'amount' => $contract->payment_amount,
                    'payment_status' => 'pending',
                    'paid_date' => null,
                    'notes' => 'One-time implementation fee',
                ]);
            }
        }

        $this->command->info('Phase 2 dummy data created successfully!');
        $this->command->info('- Partner Companies: ' . PartnerCompany::count());
        $this->command->info('- Own Companies: ' . OwnCompany::count());
        $this->command->info('- Contracts: ' . Contract::count());
        $this->command->info('- Transactions: ' . Transaction::count());
    }
}
