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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('database_host')->nullable()->after('trial_ends_at');
            $table->integer('database_port')->nullable()->after('database_host');
            $table->string('database_username')->nullable()->after('database_port');
            $table->string('database_password')->nullable()->after('database_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['database_host', 'database_port', 'database_username', 'database_password']);
        });
    }
};
