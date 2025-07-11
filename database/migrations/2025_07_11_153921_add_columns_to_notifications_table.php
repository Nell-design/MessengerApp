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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id');
            $table->string('type')->after('user_id'); // 'new_message', 'message_deleted', etc.
            $table->json('data')->after('type'); // Données spécifiques à la notification
            $table->timestamp('read_at')->nullable()->after('data');
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'type']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'type', 'data', 'read_at']);
        });
    }
};
