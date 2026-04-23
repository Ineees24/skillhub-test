<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategorieFormation extends Model
{
    use HasFactory;
    
    protected $table = 'categorieformation';

    protected $fillable = ['nomCategorie'];
}