<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\TableauEffectifsController;
use App\Http\Controllers\MediationController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ExamensController;
use App\Http\Controllers\ComptabiliteController;
use App\Http\Controllers\RessourcesHumainesController;
use App\Http\Controllers\PedagogieController;

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

//Toute les routes dans le groupes on besoin qu l'utilisateur soit identifier
/* Route::middleware(['auth'])->group(function () {

}); */

Route::get('/', [WelcomeController::class, 'index'])->name('Welcome');

//Mediation
Route::get('/Mediation/Effectifs', [TableauEffectifsController::class, 'effectifs'])->name('mediation_tableau_effectifs');
Route::post('/Mediation/Effectifs', [TableauEffectifsController::class, 'PrevisDataBase'])->name('mediation_previs_save_database');
Route::get('/Mediation/RuptureContrat', [MediationController::class, 'RuptureContrat'])->name('mediation_rupture_contrat');


//Education
Route::get('/Eductaion/welcome', [EducationController::class, 'index'])->name('eductaion_welcome');

//Examens
Route::get('/Examens/welcome', [ExamensController::class, 'index'])->name('examens_welcome');

//Ressources Humaines
Route::get('/RessourcesHumaines/welcome', [RessourcesHumainesController::class, 'index'])->name('ressources_humaines_welcome');

//Comptabilite
Route::get('/Comptabilite/welcome', [ComptabiliteController::class, 'index'])->name('comptabilite_welcome');

//Pedagogie
Route::get('/Pedagogie/welcome', [PedagogieController::class, 'index'])->name('pedagogie_welcome');
