<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RelationEntrepriseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r', [RelationEntrepriseController::class, 'index'])->name('relation_entreprise_index');

Route::get('/RelationEntreprise/{formation}/{annee}', [RelationEntrepriseController::class, 'AffichageFormation'])->name('AffichageFormation');