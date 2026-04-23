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
        Schema::create('paiement', function (Blueprint $table) {
            $table->id();
            $table->date('datePaiement')->nullable();
            $table->double('montant')->nullable();
            $table->string('refTransaction', 100)->nullable();
            $table->unsignedBigInteger('idInscription');
            $table->timestamps();
            $table->foreign('idInscription')
                  ->references('id')->on('inscription')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement');
    }
};
