<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    //

    protected function Index()
    {
        return view('welcome');
    }
}
