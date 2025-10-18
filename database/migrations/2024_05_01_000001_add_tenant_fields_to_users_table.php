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
            $table->dropUnique('users_email_unique');
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('role')->default('admin')->after('remember_token');
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('users_tenant_id_email_unique');
            $table->dropColumn(['tenant_id', 'role']);
            $table->unique('email');
        });
    }
};
