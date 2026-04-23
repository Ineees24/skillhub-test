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
        Schema::create('lecon', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->integer('duree')->nullable();
            $table->text('contenu')->nullable();
            $table->unsignedBigInteger('idFormation');
            $table->timestamps();
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
        Schema::dropIfExists('lecon');
    }
};
