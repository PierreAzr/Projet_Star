<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Previ extends Model
{
    use HasFactory;

    protected $fillable = [
        'idFormation',
        'previ',
        'periode'
    ];  
   
   //protected $guarded = [];
}
