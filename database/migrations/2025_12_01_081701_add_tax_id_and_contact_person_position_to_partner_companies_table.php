<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxIdAndContactPersonPositionToPartnerCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_companies', function (Blueprint $table) {
            $table->string('tax_id')->nullable(); // если хочешь required — не делай nullable
            $table->string('contact_person_position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_companies', function (Blueprint $table) {
            $table->dropColumn(['tax_id', 'contact_person_position']);
        });
    }
}
