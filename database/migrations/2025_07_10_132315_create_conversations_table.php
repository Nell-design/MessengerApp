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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
    $table->foreignId('first_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('second_id')->constrained('users')->onDelete('cascade');
    $table->timestamps();
    // Contrainte pour éviter les doublons (1-2 et 2-1)
    $table->unique(['first_id', 'second_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
