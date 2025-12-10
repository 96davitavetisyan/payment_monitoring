<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConvertTransactionFilesToUniversalFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Rename the table
        Schema::rename('transaction_files', 'files');

        // Add polymorphic fields
        Schema::table('files', function (Blueprint $table) {
            $table->string('fileable_type')->after('id')->nullable();
            $table->unsignedBigInteger('fileable_id')->after('fileable_type')->nullable();
            $table->index(['fileable_type', 'fileable_id']);
        });

        // Migrate existing data to use polymorphic relationship
        DB::table('files')->update([
            'fileable_type' => 'App\Models\Transaction',
            'fileable_id' => DB::raw('transaction_id')
        ]);

        // Drop the old transaction_id column
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add back transaction_id
        Schema::table('files', function (Blueprint $table) {
            $table->foreignId('transaction_id')->after('id')->nullable()->constrained('transactions')->onDelete('cascade');
        });

        // Migrate data back
        DB::table('files')
            ->where('fileable_type', 'App\Models\Transaction')
            ->update(['transaction_id' => DB::raw('fileable_id')]);

        // Remove polymorphic fields
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['fileable_type', 'fileable_id']);
            $table->dropColumn(['fileable_type', 'fileable_id']);
        });

        // Rename table back
        Schema::rename('files', 'transaction_files');
    }
}
