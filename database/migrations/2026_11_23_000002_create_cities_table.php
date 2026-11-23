<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('county_id')->constrained()->onDelete('cascade');
            $table->string('zip_code', 4)->index();
            $table->string('name', 255)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
