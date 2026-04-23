<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecon extends Model
{
    use HasFactory;

    protected $table = 'lecon';

    protected $fillable = [
        'titre',
        'duree',
        'contenu',
        'idFormation',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'idFormation');
    }
}
