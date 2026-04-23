<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formation', function (Blueprint $table) {
            $table->enum('statut', ['publié', 'brouillon'])->default('brouillon')->after('niveauRequis');
            $table->integer('vues')->default(0)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('formation', function (Blueprint $table) {
            $table->dropColumn(['statut', 'vues']);
        });
    }
};