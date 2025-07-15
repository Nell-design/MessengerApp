<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Ajoute la colonne is_deleted_for_everyone pour la gestion de la suppression globale
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'is_deleted_for_everyone')) {
                $table->boolean('is_deleted_for_everyone')->default(false)->after('deleted_for_receiver');
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_deleted_for_everyone');
        });
    }
};
