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
            $table->string('role', 20)->default('candidate')->index();
            $table->boolean('active')->default(true)->index();
            $table->boolean('is_demo')->default(false);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon', 40)->default('briefcase');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->unsignedInteger('vacancy_limit')->nullable();
            $table->json('features')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->text('bio')->nullable();
            $table->text('experience')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sector')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('verified')->default(false)->index();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements');
            $table->text('conditions');
            $table->string('schedule', 30)->index();
            $table->string('contract_type', 30)->index();
            $table->decimal('salary_min', 10, 2);
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->unsignedSmallInteger('vacancies')->default(1);
            $table->date('expires_at')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('featured')->default(false)->index();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('cv_path');
            $table->text('cover_letter')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
            $table->unique(['job_offer_id', 'user_id']);
        });

        Schema::create('job_offer_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['job_offer_id', 'user_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('job_offer_id')->constrained()->restrictOnDelete();
            $table->string('reason', 100);
            $table->text('details');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
            $table->unique(['user_id', 'job_offer_id']);
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500);
            $table->longText('body');
            $table->boolean('published')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('job_offer_user');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('candidate_profiles');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'active', 'is_demo']);
        });
    }
};
