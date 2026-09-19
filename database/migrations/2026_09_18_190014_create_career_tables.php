<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->longText('description')->nullable();
            $table->longText('description_ar')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('requirements_ar')->nullable();
            $table->longText('benefits')->nullable();
            $table->longText('benefits_ar')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->constrained('job_openings')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->foreignId('cv_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['new', 'reviewed', 'shortlisted', 'rejected', 'hired'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_openings');
    }
};
