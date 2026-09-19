<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('source_url')->nullable()->unique()->after('slug');
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('source_url')->nullable()->unique()->after('slug');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->string('source_url')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
    }
};
