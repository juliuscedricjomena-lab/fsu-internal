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
        Schema::create('disbursement_records', function (Blueprint $table) {
            $table->id();
            // Disbursement category (per Slide 6): Personnel Services, MOOE,
            // Capital Outlay, Trust Receipt, Modified Disbursement System.
            $table->string('category');
            $table->string('payee');
            $table->string('purpose');
            $table->string('reference_no');
            $table->date('disbursement_date');
            $table->decimal('amount', 15, 2);
            $table->string('attachment_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('disbursement_date');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursement_records');
    }
};
