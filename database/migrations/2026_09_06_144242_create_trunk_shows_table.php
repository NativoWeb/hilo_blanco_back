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
        Schema::create('trunk_shows', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('subtitle', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('city', 100);
            $table->string('location', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('schedule', 100)->nullable();
            $table->smallInteger('session_duration')->default(90);
            $table->smallInteger('max_guests')->default(3);
            $table->string('image', 255)->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trunk_shows');
    }
};
