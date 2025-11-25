<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Company;
use App\Models\CompanySubscription;

class ProductsAndCompaniesSeeder extends Seeder
{
    public function run()
    {
        // Create Products
        $productBasic = Product::create([
            'name' => 'Basic Plan',
            'description' => 'Entry-level payment monitoring for small businesses',
            'monthly_price' => 29.99,
            'is_active' => true
        ]);

        $productPro = Product::create([
            'name' => 'Professional Plan',
            'description' => 'Advanced features for growing companies',
            'monthly_price' => 79.99,
            'is_active' => true
        ]);

        $productEnterprise = Product::create([
            'name' => 'Enterprise Plan',
            'description' => 'Full-featured solution for large organizations',
            'monthly_price' => 199.99,
            'is_active' => true
        ]);

        // Create Companies for Basic Plan
        $companyA = Company::create([
            'name' => 'Tech Startup Inc',
            'product_id' => $productBasic->id,
            'contact_email' => 'admin@techstartup.com',
            'contact_phone' => '+1-555-0101',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyA->id,
            'starts_from' => now()->subMonths(3),
            'price_per_month' => 29.99,
            'status' => 'active'
        ]);

        $companyB = Company::create([
            'name' => 'Digital Services LLC',
            'product_id' => $productBasic->id,
            'contact_email' => 'billing@digitalservices.com',
            'contact_phone' => '+1-555-0102',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyB->id,
            'starts_from' => now()->subMonths(1),
            'price_per_month' => 29.99,
            'status' => 'active'
        ]);

        // Create Companies for Professional Plan
        $companyC = Company::create([
            'name' => 'Global Trade Corp',
            'product_id' => $productPro->id,
            'contact_email' => 'accounts@globaltrade.com',
            'contact_phone' => '+1-555-0201',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyC->id,
            'starts_from' => now()->subMonths(6),
            'price_per_month' => 79.99,
            'status' => 'active'
        ]);

        $companyD = Company::create([
            'name' => 'Innovation Labs',
            'product_id' => $productPro->id,
            'contact_email' => 'finance@innovationlabs.com',
            'contact_phone' => '+1-555-0202',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyD->id,
            'starts_from' => now()->subMonths(2),
            'price_per_month' => 79.99,
            'status' => 'active'
        ]);

        $companyE = Company::create([
            'name' => 'Market Leaders Group',
            'product_id' => $productPro->id,
            'contact_email' => 'payments@marketleaders.com',
            'contact_phone' => '+1-555-0203',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyE->id,
            'starts_from' => now()->subMonth(),
            'price_per_month' => 79.99,
            'status' => 'active'
        ]);

        // Create Companies for Enterprise Plan
        $companyF = Company::create([
            'name' => 'Enterprise Solutions International',
            'product_id' => $productEnterprise->id,
            'contact_email' => 'accounting@esi.com',
            'contact_phone' => '+1-555-0301',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyF->id,
            'starts_from' => now()->subYear(),
            'price_per_month' => 199.99,
            'status' => 'active'
        ]);

        $companyG = Company::create([
            'name' => 'Fortune Enterprises',
            'product_id' => $productEnterprise->id,
            'contact_email' => 'billing@fortune-ent.com',
            'contact_phone' => '+1-555-0302',
            'is_active' => true
        ]);

        CompanySubscription::create([
            'company_id' => $companyG->id,
            'starts_from' => now()->subMonths(8),
            'price_per_month' => 199.99,
            'status' => 'active'
        ]);
    }
}
