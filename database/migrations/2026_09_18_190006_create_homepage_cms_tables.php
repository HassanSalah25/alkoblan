<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->nullable();
            $table->string('tag_ar')->nullable();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('subtitle')->nullable();
            $table->text('subtitle_ar')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_url')->nullable();
            $table->string('button2_text')->nullable();
            $table->string('button2_text_ar')->nullable();
            $table->string('button2_url')->nullable();
            $table->foreignId('image_desktop_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('image_mobile_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('subtitle_ar')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_ar')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('button_text')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_url')->nullable();
            $table->json('extra')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('position')->nullable();
            $table->string('position_ar')->nullable();
            $table->string('company')->nullable();
            $table->string('company_ar')->nullable();
            $table->text('content');
            $table->text('content_ar')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('famous_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->foreignId('logo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('famous_clients');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('hero_slides');
    }
};
