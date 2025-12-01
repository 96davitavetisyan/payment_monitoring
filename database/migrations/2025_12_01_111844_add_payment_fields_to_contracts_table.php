<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('account_number')->after('payment_amount');
            $table->date('payment_date')->nullable()->after('account_number');
            $table->date('payment_finish_date')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['account_number', 'payment_date', 'payment_finish_date']);
        });
    }
}
