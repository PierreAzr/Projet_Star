<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RessourcesHumainesController extends Controller
{
    //
    public function index()
    {
        return view('ressourceshumaines/welcome');
    }
}
