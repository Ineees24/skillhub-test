<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Methode vide intentionnellement, pas de services à enregistrer pour le moment
        //Accepté par laravel
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // On active les foreign keys pour SQLite 
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
}
