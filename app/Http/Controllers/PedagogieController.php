<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PedagogieController extends Controller
{
    //
    public function index()
    {
        return view('pedagogie/welcome');
    }
}
