<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('type', ['admin', 'customer'])->default('customer')->after('phone');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');
            $table->string('company')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'type', 'status', 'company']);
            $table->dropSoftDeletes();
        });
    }
};
