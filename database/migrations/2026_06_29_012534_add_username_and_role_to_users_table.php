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
        $table->string('username')->unique()->after('name');
        $table->enum('role', ['owner', 'karyawan'])->default('karyawan')->after('username');
        $table->boolean('is_active')->default(true)->after('role');
        $table->timestamp('last_login_at')->nullable()->after('is_active');
        $table->dropColumn('email');
        $table->dropColumn('email_verified_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['username', 'role', 'is_active', 'last_login_at']);
        $table->string('email')->unique()->after('name');
        $table->timestamp('email_verified_at')->nullable();
    });
}
};
