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
            $table->foreignId('tenant_id')->after('id')->constrained()->onDelete('cascade');
            $table->string('role')->default('user')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            
            // Make email unique per tenant instead of globally
            $table->dropUnique(['email']);
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique(['email']);
            $table->dropColumn(['tenant_id', 'role', 'is_active']);
        });
    }
};
