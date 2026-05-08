<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idUtilisateur')->constrained('users')->onDelete('cascade');
            $table->foreignId('idFormation')->constrained('formation')->onDelete('cascade');
            $table->tinyInteger('note')->unsigned();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['idUtilisateur', 'idFormation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
};