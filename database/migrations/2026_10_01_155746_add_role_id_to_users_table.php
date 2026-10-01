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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->onDelete('set null');
            $table->string('designation')->nullable()->after('name');
            $table->string('rank')->nullable()->after('designation');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['role_id']);
            $table->dropColumn(['role_id', 'designation', 'rank', 'status']);
        });
    }
};
