<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bidang_tanah');
    }

    public function down(): void
    {
        Schema::create('bidang_tanah', function (Blueprint $table) {
            $table->id();
            $table->string('nib')->nullable()->unique();
            $table->decimal('area', 15, 2)->default(0);
            $table->string('region')->nullable();
            $table->string('land_use')->nullable();
            $table->string('right_type', 100)->nullable();
            $table->string('quality')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->json('geometry')->nullable();
            $table->string('status')->default('belum_terpetakan');
            $table->timestamps();
        });
    }
};
