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
        Schema::table('roles', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['tenant_id']);
            // Drop the column
            $table->dropColumn('tenant_id');
            // Remove unique constraint that included tenant_id
            $table->dropUnique(['name', 'tenant_id']);
            // Add back unique constraint for name only
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Remove name unique constraint
            $table->dropUnique(['name']);
            // Add tenant_id back
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            // Add back the compound unique constraint
            $table->unique(['name', 'tenant_id']);
        });
    }
};
