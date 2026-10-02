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
        Schema::create('collection_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('account_type');
            $table->string('payor_name');
            $table->date('transaction_date');
            $table->string('or_number');
            $table->string('or_attachment_path')->nullable();
            $table->decimal('amount', 15, 2);
            // Who prepared/recorded the transaction.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_transactions');
    }
};
