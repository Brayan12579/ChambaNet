<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            $table->boolean('cv_required')->default(true);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable paths: historical applications without a CV must remain valid.
        Schema::table('job_offers', function (Blueprint $table) {
            $table->dropColumn('cv_required');
        });
    }
};
