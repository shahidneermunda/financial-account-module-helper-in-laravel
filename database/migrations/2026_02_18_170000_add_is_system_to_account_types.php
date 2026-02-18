<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_active');
        });

        // Mark default account types as system
       // DB::table('account_types')->whereIn('code', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])
         //   ->update(['is_system' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
