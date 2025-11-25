<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTransactionsTableAddNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, rename the column
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('customer_name', 'company_name');
        });
        
        // Then add new columns
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('person_name')->nullable()->after('company_name');
            $table->date('max_overdue_date')->nullable()->after('transaction_date');
            $table->enum('transaction_type', ['one-time', 'monthly'])->default('one-time')->after('max_overdue_date');
            $table->date('contract_start_date')->nullable()->after('transaction_type');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->string('contract_file')->nullable()->after('contract_end_date');
        });
        
        // Update payment_status enum to include 'overdue'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('paid','unpaid','late','overdue','notified') DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop added columns first
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'person_name',
                'max_overdue_date',
                'transaction_type',
                'contract_start_date',
                'contract_end_date',
                'contract_file'
            ]);
        });
        
        // Then rename back
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('company_name', 'customer_name');
        });
        
        // Revert payment_status enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('paid','unpaid','late','notified') DEFAULT 'unpaid'");
    }
}
