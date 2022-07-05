<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RelationEntrepriseController;
use App\Http\Controllers\WelcomeController;
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

//a supprimer a la fin
use App\Http\Controllers\testRelationEntrepriseController;
Route::get('/test', [testRelationEntrepriseController::class, 'index'])->name('relation_entreprise_index_test');

Route::get('/', [WelcomeController::class, 'index'])->name('Welcome');

Route::get('/r', [RelationEntrepriseController::class, 'index'])->name('relation_entreprise_index');
Route::post('/r', [RelationEntrepriseController::class, 'PrevisDataBase'])->name('previs_save_database');

Route::get('/RelationEntreprise', [RelationEntrepriseController::class, 'AffichageFormation'])->name('affichage_formation');