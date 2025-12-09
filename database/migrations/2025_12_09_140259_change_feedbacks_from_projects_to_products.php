<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFeedbacksFromProjectsToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            // Drop old foreign key and column
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');

            // Add new column and foreign key
            $table->foreignId('product_id')->after('id')->constrained('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            // Drop new foreign key and column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            // Restore old column and foreign key
            $table->foreignId('project_id')->after('id')->constrained('projects')->onDelete('cascade');
        });
    }
}
