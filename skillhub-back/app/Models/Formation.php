<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formation extends Model
{
    use HasFactory;

    protected $table = 'formation';

    protected $fillable = [
        'titre',
        'description',
        'duree',
        'prix',
        'niveauRequis',
        'vues',
        'idUtilisateur',
        'idCategorie',
    ];

    // Retourne le formateur  qui a créé cette formation.
    public function formateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur');
    }

    // Retourne la catégorie à laquelle appartient cette formation.
    public function categorie()
    {
        return $this->belongsTo(CategorieFormation::class, 'idCategorie');
    }

    // Retourne les modules (lecons) de cette formation.
    public function lecons()
    {
        return $this->hasMany(Lecon::class, 'idFormation');
    }
}
