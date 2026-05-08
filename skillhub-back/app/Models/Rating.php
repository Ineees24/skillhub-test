<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'rating';

    protected $fillable = [
        'idUtilisateur',
        'idFormation',
        'note',
        'commentaire',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'idFormation');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idUtilisateur');
    }
}