<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComptabiliteController extends Controller
{
    //
    public function index()
    {
        return view('comptabilite/welcome');
    }
}
