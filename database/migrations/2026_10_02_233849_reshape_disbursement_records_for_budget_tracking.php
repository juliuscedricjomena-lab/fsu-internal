<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reshape disbursement_records to match the PPT budget-tracking model
     * (slides 6-8): fund type, expense-class hierarchy, project particulars,
     * and mode of disbursement.
     */
    public function up(): void
    {
        Schema::table('disbursement_records', function (Blueprint $table) {
            // Appropriated vs Non-Appropriated (Trust) funds (slide 7).
            $table->string('fund_type')->default('appropriated')->after('id');
            // Top-level grouping: PS / MOOE / CO / Trust.
            $table->string('expense_group')->after('fund_type');
            // Specific line item within the group.
            $table->string('expense_class')->after('expense_group');
            // Free-text project detail, used mainly for Capital Outlay (slide 8).
            $table->string('project_particular')->nullable()->after('expense_class');
            // e.g. "LDDAP ADA", "Check", "Cash".
            $table->string('mode_of_disbursement')->nullable()->after('amount');

            $table->index('fund_type');
            $table->index('expense_group');
        });

        // Best-effort migrate any existing rows: old `category` becomes the
        // expense_class, with a group/fund mapping.
        $map = [
            'Personnel Services' => 'PS',
            'MOOE' => 'MOOE',
            'Capital Outlay' => 'CO',
            'Trust Receipt' => 'Trust',
            'Modified Disbursement System' => 'MOOE',
        ];

        foreach (DB::table('disbursement_records')->get() as $row) {
            $category = $row->category ?? 'MOOE';
            DB::table('disbursement_records')->where('id', $row->id)->update([
                'expense_group' => $map[$category] ?? 'MOOE',
                'expense_class' => $category,
                'project_particular' => $row->purpose ?? null,
                'fund_type' => $category === 'Trust Receipt' ? 'non_appropriated' : 'appropriated',
            ]);
        }

        Schema::table('disbursement_records', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('purpose');
        });
    }

    public function down(): void
    {
        Schema::table('disbursement_records', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('purpose')->nullable();
            $table->dropIndex(['fund_type']);
            $table->dropIndex(['expense_group']);
            $table->dropColumn(['fund_type', 'expense_group', 'expense_class', 'project_particular', 'mode_of_disbursement']);
        });
    }
};
