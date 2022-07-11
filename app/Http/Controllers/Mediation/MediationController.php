<?php

namespace App\Http\Controllers\Mediation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediationController extends Controller
{
    public function RuptureContrat()
    {
        return view('mediation/rupturecontrat');
    }
}
