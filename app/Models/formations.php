<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class formations extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomSecteurActivite',
        'nomFormation',
        'nomAnnee',
        'capaciteMax',
    ];  

   //protected $guarded = [];
}
