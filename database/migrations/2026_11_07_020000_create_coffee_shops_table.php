<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coffee_shops', function (Blueprint $table) {
            $table->id();
            $table->string('source_key')->nullable()->unique();
            $table->string('license_number')->nullable()->index();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('ward')->nullable()->index();
            $table->string('business_code', 20)->nullable();
            $table->string('business_type')->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_accuracy')->default('Perkiraan kelurahan');
            $table->string('opening_hours')->nullable();
            $table->unsignedSmallInteger('source_year')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coffee_shops');
    }
};
