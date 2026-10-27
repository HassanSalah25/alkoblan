<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipe_planning_projects', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Guest ownership: sha256 of a random token kept in the guest's session.
            $table->string('guest_token_hash', 64)->nullable()->index();
            $table->string('project_type', 30);
            $table->string('project_status', 30);
            $table->string('system_type', 30);
            $table->string('scope', 20)->default('complete');
            $table->json('fixtures')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('unit', 5)->default('m');
            $table->json('measurements')->nullable();
            $table->json('answers')->nullable();
            $table->decimal('waste_allowance_percent', 5, 2)->nullable();
            $table->string('locale', 5)->default('ar');
            $table->string('analysis_status', 20)->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('pipe_planning_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('pipe_planning_projects')->cascadeOnDelete();
            $table->string('storage_key');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 50);
            $table->unsignedInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('sha256', 64);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('caption', 300)->nullable();
            $table->boolean('has_scale_reference')->default(false);
            $table->string('processing_status', 20)->default('stored');
            $table->timestamps();
            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('pipe_planning_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('pipe_planning_projects')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('input_hash', 64)->index();
            $table->string('provider', 30)->default('openrouter');
            $table->string('model')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->string('stage', 30)->nullable();
            $table->json('observations')->nullable();
            $table->json('routing')->nullable();
            $table->json('assumptions')->nullable();
            $table->json('missing_information')->nullable();
            $table->json('clarification_questions')->nullable();
            $table->json('safety_flags')->nullable();
            $table->string('readiness', 40)->nullable();
            $table->decimal('waste_allowance_percent', 5, 2)->nullable();
            $table->string('validation_status', 20)->nullable();
            $table->json('validation_errors')->nullable();
            $table->string('failure_category', 40)->nullable();
            $table->string('provider_request_id')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'version']);
        });

        Schema::create('pipe_planning_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('pipe_planning_analyses')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('category', 20);
            $table->string('water', 10)->nullable();
            $table->unsignedSmallInteger('diameter_mm')->nullable();
            $table->unsignedSmallInteger('secondary_diameter_mm')->nullable();
            $table->decimal('base_quantity', 12, 3)->nullable();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit', 10);
            $table->string('quantity_status', 30);
            $table->string('match_status', 30);
            $table->string('source', 30);
            $table->json('derivation')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pipe_planning_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('pipe_planning_projects')->cascadeOnDelete();
            $table->foreignId('analysis_id')->unique()->constrained('pipe_planning_analyses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_message_id')->nullable()->constrained('contact_messages')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->json('items');
            $table->json('approved_assumptions')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->timestamps();
        });

        // Navigation entry for existing installs (MenuSeeder covers fresh installs).
        if (Schema::hasTable('menu_items')
            && ! DB::table('menu_items')->where('url', '/pipe-planner')->exists()) {
            $contact = DB::table('menu_items')
                ->where('location', 'main')->whereNull('parent_id')->where('url', '/contact')->first();
            DB::table('menu_items')->insert([
                'location' => 'main', 'title' => 'AI Pipe Planner', 'title_ar' => 'مخطط المواسير الذكي',
                'url' => '/pipe-planner', 'sort_order' => $contact ? max(0, $contact->sort_order - 1) : 90,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')->where('url', '/pipe-planner')->delete();
        }
        Schema::dropIfExists('pipe_planning_quote_requests');
        Schema::dropIfExists('pipe_planning_items');
        Schema::dropIfExists('pipe_planning_analyses');
        Schema::dropIfExists('pipe_planning_images');
        Schema::dropIfExists('pipe_planning_projects');
    }
};
