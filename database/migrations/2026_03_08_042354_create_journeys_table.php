<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->text('wish');
            $table->decimal('departure_lat', 10, 7);
            $table->decimal('departure_lng', 10, 7);
            $table->string('departure_name');
            $table->decimal('distance_km', 7, 1);
            $table->integer('estimated_days');
            $table->date('departed_at');
            $table->json('story')->nullable();
            $table->json('route_places')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};
