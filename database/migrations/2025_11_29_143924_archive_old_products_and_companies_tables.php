<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchiveOldProductsAndCompaniesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Archive old tables by renaming them
        Schema::rename('products', 'products_archive');
        Schema::rename('companies', 'companies_archive');
        Schema::rename('company_subscriptions', 'company_subscriptions_archive');
        Schema::rename('payments', 'payments_archive');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore archived tables
        Schema::rename('products_archive', 'products');
        Schema::rename('companies_archive', 'companies');
        Schema::rename('company_subscriptions_archive', 'company_subscriptions');
        Schema::rename('payments_archive', 'payments');
    }
}
