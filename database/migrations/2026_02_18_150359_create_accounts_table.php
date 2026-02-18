<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_type_id')->constrained('account_types')->onDelete('restrict');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // For storing additional data like tax info, etc.
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['account_type_id', 'is_active']);
            $table->index(['parent_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
