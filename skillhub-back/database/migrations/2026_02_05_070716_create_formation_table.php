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
        Schema::create('formation', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->text('description')->nullable();
            $table->integer('duree')->nullable();
            $table->double('prix')->nullable();
            $table->string('niveauRequis', 100)->nullable();
            $table->unsignedBigInteger('idUtilisateur');
            $table->unsignedBigInteger('idCategorie')->nullable();
            $table->timestamps();
            $table->foreign('idUtilisateur')
                  ->references('id')->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('idCategorie')
                  ->references('id')->on('categorieformation')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formation');
    }
};
