<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('featured_image_alt')->nullable()->after('featured_image_id');
            $table->string('featured_image_alt_ar')->nullable()->after('featured_image_alt');
            $table->string('canonical_url')->nullable()->after('seo_keywords_ar');
            $table->string('canonical_url_ar')->nullable()->after('canonical_url');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['featured_image_alt', 'featured_image_alt_ar', 'canonical_url', 'canonical_url_ar']);
        });
    }
};
