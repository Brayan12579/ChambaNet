<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('district_id')->nullable()->change();
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('work_mode', 12)->default('onsite');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        // Permite distritos nulos para conservar las ofertas remotas o fuera del catálogo local al revertir la migración.
        Schema::table('job_offers', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn(['location_name', 'latitude', 'longitude', 'work_mode']);
        });
    }
};
