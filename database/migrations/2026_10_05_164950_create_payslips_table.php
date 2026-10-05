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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            // PAYSLIP / READMITTED / TURNBACK (slides 11-13).
            $table->string('status');
            // Period the payslip covers, stored as the first day of the month.
            $table->date('period_month');

            // Computation inputs (slide 12: total payslip minus days of duty).
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('working_days')->nullable();
            $table->unsignedSmallInteger('days_present')->nullable();
            $table->unsignedSmallInteger('days_absent')->nullable();
            $table->decimal('computed_amount', 15, 2)->nullable();

            // Payslip stored as a PDF file.
            $table->string('pdf_path')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('period_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
