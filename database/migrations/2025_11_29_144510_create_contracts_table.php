<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_company_id')->constrained('partner_companies')->onDelete('cascade');
            $table->foreignId('own_company_id')->constrained('own_companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('contract_number')->unique()->nullable();
            $table->date('contract_start_date');
            $table->date('contract_end_date')->nullable();
            $table->enum('payment_type', ['one-time', 'monthly'])->default('monthly');
            $table->decimal('payment_amount', 12, 2);
            $table->enum('status', ['active', 'completed', 'cancelled', 'suspended'])->default('active');
            $table->string('contract_file')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
