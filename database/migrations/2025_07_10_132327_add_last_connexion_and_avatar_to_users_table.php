<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_connexion')) {
                $table->timestamp('last_connexion')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('last_connexion');
            }
            // Si vous voulez vraiment positionner les colonnes après remember_token,
            // vous devrez recréer la table ou utiliser une approche différente
        });
    }
 
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_connexion', 'avatar']);
        });
    }
};
 