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
        Schema::create('inscription', function (Blueprint $table) {
            $table->id();
            $table->date('dateInscription')->nullable();
            $table->string('statut', 50)->nullable();
            $table->unsignedBigInteger('idUtilisateur');
            $table->unsignedBigInteger('idFormation');
            $table->timestamps();
            $table->foreign('idUtilisateur')
                ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('idFormation')
                  ->references('id')->on('formation')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscription');
    }
};
