<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\ApiRequestTrait;

class AppController extends Controller
{
    public function CacheSchedule()
    {
       //Fonction jouer automatiquement en schedule
       //Mettre les fonctions des requette api dans ApiRequestTrait que l'on veut mettre en cache automatiquement
       $this->ApiPeriodes();
       $this->ApiEntreprises();

    }
}
