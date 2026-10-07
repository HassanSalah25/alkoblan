<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('slug_ar')->nullable()->unique()->after('slug');
            $table->string('seo_title_ar')->nullable()->after('seo_title');
            $table->text('seo_description_ar')->nullable()->after('seo_description');
            $table->string('seo_keywords_ar')->nullable()->after('seo_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['slug_ar', 'seo_title_ar', 'seo_description_ar', 'seo_keywords_ar']);
        });
    }
};
