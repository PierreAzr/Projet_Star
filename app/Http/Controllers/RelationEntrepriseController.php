<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\ApiRequestTrait;

//pour utilise requete api
use Illuminate\Support\Facades\Http;
//mise en cache
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class RelationEntrepriseController extends Controller
{
    
    use ApiRequestTrait;

    public $tab;

    public function index(Request $request)
    {   

        //date_default_timezone_set('Europe/Paris');

        //vider tous le cache
        //Cache::flush();
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '512M' );
        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);


        
        //Recuperation de la table periode 
        //trouver la periode qui correspond a la date voulu (date du jour par defaut) 
        //ainsi que la periode n-1
        //##############################################################"
        
        //On recupere la date s'il y en a une
        $date = $request->get('date');

        if(empty($date)){
            echo("ttttt dateempty tttttt");
            $date_vide = true;
            $date = date("Y-m-d");
            $date_du_jour = date_create();
            $date_annee_precedente = date_create()->modify('-1 year');
            //$date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );
            //$date_annee_precedente = date_create_from_format('d/m/Y', date("d/m/Y") )->modify('-1 year');

        }else {

            $date_du_jour = date_create_from_format('Y-m-d', $date ) ;
            $date_annee_precedente = date_create_from_format('Y-m-d', $date )->modify('-1 year');
        }

        //Requete api
        $api_data_periodes = $this->ApiPeriodes();
        //dd($api_data_periodes);

        foreach ($api_data_periodes as $periode) {

            $date_debut_periode = date_create_from_format('d/m/Y', $periode["dateDeb"] );
            $date_fin_periode = date_create_from_format('d/m/Y', $periode["dateFin"] );

            if ($date_debut_periode <= $date_du_jour && $date_du_jour <= $date_fin_periode) {
                $code_periode_actuel = $periode["codePeriode"];
                $periode_actuel = $periode["nomPeriode"];
            }

            //periode precedente
            if ($date_debut_periode <= $date_annee_precedente && $date_annee_precedente <= $date_fin_periode) {
                $code_periode_precedente = $periode["codePeriode"];
            }
            
        }
        // Fin calcul Periode

        $previs = \App\Models\Previ::where('periode', $periode_actuel)->get();

        /*         
        $previs = \App\Models\Previ::where('periode', $periode_actuel)->get();
        $formations = \App\Models\Formations::all();

        $test = \App\Models\Formations::join('previs', 'previs.idFormation', '=', 'formations.id')
            ->where('previs.periode', $periode_actuel)
            ->get(['formations.*', 'previs.*']);

        foreach ($test as $key => $value) {
            dd($value['periode']);
            
        } */
        
        if(!isset($code_periode_actuel)){
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond a une periode qui n'existe pas encore")
            ->with('flash_type', 'alert-danger');
        }

        $previs = \App\Models\Previ::where('periode', $periode_actuel)->get();

        $date_vide = null;
        if(!empty($date_vide)){
            echo("***********datevide***********");
            $tableau_complet = Cache::get('tableau_complet_date_vide');
            $final_tab = Cache::get('final_tab_date_vide');
            $total_tab = Cache::get('total_tab_date_vide');     

            if (!empty($tableau_complet) && !empty($final_tab) && !empty($total_tab)) {
         
                echo("***********datevide***********");
                return view('relationentreprise')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('tableau_complet'))
                ->with(compact('previs'))
                ->with(compact('date'))
                ->with(compact('periode_actuel'));
            }

        }

        //$api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);

         $api_data_frequentes = Cache::get('api_data_frequentes');
        if (empty($api_data_frequentes)) {
            $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);
            Cache::put('api_data_frequentes', $api_data_frequentes, 32000);
        }   

        $count = 0;
        $frequente_tab=[];
        foreach ($api_data_frequentes as $frequente) {

            $date_fin = date_create_from_format('d/m/Y', $frequente["dateFin"]);
            $date_deb = date_create_from_format('d/m/Y', $frequente["dateDeb"]);
            if( (empty($frequente["dateFin"]) || $date_du_jour < $date_fin) && ($date_du_jour > $date_deb)) {
                
                $frequente_tab[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );
                $count++;
            }
        }
 
        $apprenants_tab = $this->ApprenantsTab($date_du_jour,$code_periode_actuel,$code_periode_precedente, $frequente_tab);




        #########################
        ###########
     /*    $date_debut_prospect = $date_annee_precedente->format('d-m-Y');
        $date_fin_prospect = $date_du_jour->format('d-m-Y');
        $this->prospectstest($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab= null,$apprenants_tab);
        exit; */
         #############################################################


        //##Recupere la table des prospects voulu##
 
        $date_debut_prospect = date_create()->modify('-1 year')->format('d-m-Y');
        $date_fin_prospect = date('d-m-Y');
        //$date_debut_prospect = $date_debut_periode->format('d-m-Y');
        //$date_fin_prospect = $date_fin_periode->format('d-m-Y');

        $prospects_tab_temp = $this->ProspectsTab($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab);
        if (isset($prospects_tab_temp)) {
            $prospects_tab = $prospects_tab_temp['prospects_tab'];
            $prospects_plusieurs_formation = $prospects_tab_temp['prospects_plusieurs_formation'];
        }
       
        //dd($prospects_tab_temp);
        

        //############################################################################
        //############################################################################
        //$this->testfrequantation($prospects_tab);
        //$this->testCommun($apprenants_tab,$prospects_tab);
        //$this->test($code_periode_actuel, $date_annee_precedente, $date_du_jour, $frequente_tab);
        //$this->frequentUnique();
        //exit;
        //############################################################################
        //############################################################################  

        if (isset($prospects_tab) && isset($apprenants_tab)) {
            
            $tableau_complet = array_merge($prospects_tab, $apprenants_tab);
            //dump($tableau_complet);

            //$tableau_complet = $apprenants_tab + $prospects_tab;
            //dd($tableau_complet);

        }elseif (isset($apprenants_tab)) {
            //dd('ok');
            $tableau_complet = $apprenants_tab;
            Session::flash('message', 'Table prospects vide'); 
            Session::flash('alert-class', 'alert-danger'); 
        }elseif (isset($prospects_tab)){
            $tableau_complet = $prospects_tab;
            Session::flash('message', 'Table apprenants vide'); 
            Session::flash('alert-class', 'alert-danger');  
        }else{
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond a une periode qui existe mais il n'y a encore ni prospects ni apprenant")
            ->with('flash_type', 'alert-danger');  
        }
       
        
        $liste_tableau = $this->ConstructionTableauFinal($tableau_complet, $periode_actuel);
            $final_tab = $liste_tableau['final_tab'];
            $total_tab = $liste_tableau['total_tab'];
        
        //##erreur
        //$erreur = $this->Erreur($tableau_complet);


        // mise en cache tableau complet, final_tab et total_tab dans le cas ou la date est vide et donc on prend la date du jour
        if(isset($date_vide)){
            echo('sesesese  set date vide seseseeseees');
            Cache::put('final_tab_date_vide', $final_tab, 32000);
            Cache::put('total_tab_date_vide', $total_tab, 32000);
            Cache::put('tableau_complet_date_vide', $tableau_complet, 32000);
        }

        //$periode_actuel_cache = Cache::get('periode_cache');

        /* // mise en cache tableau complet, final_tab et total_tab avec la periode qui correspond
        if(isset($date_vide)){
            Cache::put('periode_cache', $periode_actuel, 32000);
            Cache::put('final_tab_cache', $final_tab, 32000);
            Cache::put('total_tab_cache', $total_tab, 32000);
            Cache::put('tableau_complet_cache', $tableau_complet, 32000);
        } */

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo("temps execution : {$time}, Memoire utlisé : {$memory}"); 

        return view('relationentreprise')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('tableau_complet'))
                //->with(compact('previs'))
                ->with(compact('date'))
                ->with(compact('periode_actuel'));

    }




//############################################################################
//############################################################################
//############################################################################

    public function ConstructionTableauFinal($tableau_complet, $periode_actuel)
    {

         $formations = \App\Models\Formations::join('previs', 'previs.idFormation', '=', 'formations.id')
        ->where('previs.periode', $periode_actuel)
        ->get(['formations.*', 'previs.*']); 
            
        $final_tab = [];
        $pre_contrat_total=0;
        $reception_contrat_total=0;
        $contrat_recu_total=0;
        $nouveau_total=0;
        $ancient_total=0;
        $count_total=0;

        foreach ($formations as $formation) {

            $pre_contrat=0;
            $reception_contrat=0;
            $contrat_recu=0;
            $nouveau=0;
            $ancient=0;
            $count=0;

            foreach ($tableau_complet as $individu) {

                if($individu['nomFormation'] == $formation["nomFormation"]  && $individu["nomAnnee"] == $formation["nomAnnee"]){
                
                    if (!empty($individu['codeEtapeEvenement'])) {

                        if($individu['codeEtapeEvenement'] == 151){
                            $pre_contrat++;
                            $pre_contrat_total++;
                        };

                        if($individu['codeEtapeEvenement'] == 149){
                            $reception_contrat++;
                            $reception_contrat_total++;

                        };

                        if($individu['codeEtapeEvenement'] == 8){
                            $contrat_recu++;
                            $contrat_recu_total++;
                        };
                    }else{
                        if($individu['nouveau'] == 1){
                            $nouveau++;
                            $nouveau_total++;
                        }else{
                            $ancient++;
                            $ancient_total++;
                        };
                    };
                    $count++;
                    $count_total++;
                };
            }

            //tableau final contenant
            array_push($final_tab, array(
                "idFormation" => $formation["id"],
                "nomSecteurActivite" => $formation["nomSecteurActivite"], 
                "nomFormation" => $formation["nomFormation"],
                "nomAnnee" => $formation["nomAnnee"],
                "previ" => $formation["previ"],
                "previTotal" => $count + $formation["previ"],
                "preContrat" => $pre_contrat,
                "receptionContrat" => $reception_contrat,
                "contratRecu" => $contrat_recu,
                "nouveau" => $nouveau,
                "ancient" => $ancient,
                "total" => $count,
                "capaciteMax" => $formation["capaciteMax"],
                "nbPlacePossible" => $formation["capaciteMax"] - $count         

            )
            );

        }

        // tableau contenant le total des colonnes
        $total_tab = array(              
                        "preContrat" => $pre_contrat_total,
                        "receptionContrat" => $reception_contrat_total,
                        "contratRecu" => $contrat_recu_total,
                        "nouveau" => $nouveau_total,
                        "ancient" => $ancient_total,
                        "total" => $count_total,
        );
        

        return array("final_tab" => $final_tab, "total_tab" => $total_tab );
    }


    protected function ProspectEvenement($codeEvenement, $date_debut_prospect, $date_fin_prospect )
    {
        // Fonction appelant une requete api sur les prospects en fonction de leur code evenement aisin que des dates de fin et debut.
        // on retoune un tableau contenant seulement le code apprenant.


        $date_debut_prospect = date_create()->modify('-1 year')->format('d-m-Y');
        $date_fin_prospect = date('d-m-Y');

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        // codeTypeEvt trouver avec la table typeEvenement https://citeformations.ymag.cloud/index.php/r/v1/types-evenement
        $api_data_prospect_evenement = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);  

        //tableau qui recupere seulement le code apprenant
        foreach ($api_data_prospect_evenement as $prospect) {
            $tab_data_prospect_evenement[$prospect["codeApprenant"]] = array(
                "codeApprenant" => $prospect["codeApprenant"]
            );
        }

        // Le tableau peu etre null
        if (isset($tab_data_prospect_evenement)) {
            return $tab_data_prospect_evenement;
        }else {
            return  null;
        }
    }

    protected function ProspectsTab($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab= null)
    {
        // Premiere requete API on recupere la table des prospects correspondant a la periode scolaire
        // Aucune distinction possible entre les prospects cloturer ou non
        $api_data_prospects = $this->ApiProspects($code_periode_actuel);
         
        // on fais Trois requetes api (requete differente de la premiere)
        // on recupere le code apprenant des prospects avec l'evenement voulu et on met en cache

        $prospects_tab_recu = Cache::get('prospects_tab_recu');
        if (empty($prospects_tab_recu)) {
            $prospects_tab_recu = $this->ProspectEvenement($codeEvenement=8, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_recu', $prospects_tab_recu, 32000);
        }

        $prospects_tab_reception = Cache::get('prospects_tab_reception');
        if (empty($prospects_tab_reception)) {

            $prospects_tab_reception = $this->ProspectEvenement($codeEvenement=151, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_reception', $prospects_tab_reception, 32000);
        }

        $prospects_tab_envoi = Cache::get('prospects_tab_envoi');
        if (empty($prospects_tab_envoi)) {

            $prospects_tab_envoi = $this->ProspectEvenement($codeEvenement=149, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_envoi', $prospects_tab_envoi, 32000);
        }
        
        //Creation de la table prospects voulu
        $count = 0;
        $count2 = 0;
        foreach ($api_data_prospects as $codeApprenant => $prospect) {


            $code_app = $prospect["codeApprenant"];
            if (!empty($prospects_tab_envoi[$code_app]) || !empty($prospects_tab_recu[$code_app]) || !empty($prospects_tab_reception[$code_app]) ) {
               
                // un prospect ne peu pas etre en cours de formation
                if (empty($frequente_tab[$code_app])) {
                   
                $count++;

                    //un prospect peu avoir plusieurs evenement racine, on prend le dernier qui correspond au dernier en date
                    $nombre_evenement_racine = count($prospect["evenementsRacines"]); 
                    $dernier_evenement_racine = $prospect["evenementsRacines"][$nombre_evenement_racine - 1]; 

                    // si un evenement choisi c'est passe sur la periode mais n'est pas le dernier
                    // on verifie que le dernier evenement et un des bon codeEtape
                    $codeEtape = $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"];
                    if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {
                
                        //Construction du tableau prsopects
                        //Attention certain prospect on plusieur formation souhaite on prend la premiere 
                        $prospects_tab[$prospect["codeApprenant"]] = array(
                            "nomApprenant" => $prospect["nomApprenant"],
                            "prenomApprenant" => $prospect["prenomApprenant"],
                            "nomFormation" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"],
                            "nomAnnee" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomAnnee"],
                            "nomStatut" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomStatut"],
                            "codeEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"],
                            "nomEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["nomEtapeEvenement"],

                        );

                        $nombre_formation = count($dernier_evenement_racine["formationsSouhaitees"]);
                        if ( $nombre_formation > 1) {
                            $prospects_plusieurs_formation[$prospect["codeApprenant"]] = $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"];
                        /*                            
                            for ($i=0; $i < $nombre_formation ; $i++) {
                                $prospects_plusieurs_formation[$prospect["codeApprenant"]] += array(

                                    "nomFormation$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomFormation"],
                                    "nomAnnee$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomAnnee"],

                                );
                            } */
                            
                        }        

                    }else{
                        //653791
                        //dump($prospect);
                    }
                    
                }
            }
    
        }
        //dd($prospects_tab);
        /* dump($count);
        dump($count2);
        $prospect_evenement = array_merge($prospects_tab_envoi , $prospects_tab_reception, $prospects_tab_recu );
        dump($prospect_evenement);
        exit;  */
        $prospects_plusieurs_formation = null;
        //$prospect_evenement = array_merge($prospects_tab_envoi , $prospects_tab_reception, $prospects_tab_recu );
        //$prospects_tab = null;
        if (isset($prospects_tab)) {
            return array("prospects_tab" => $prospects_tab, "prospects_plusieurs_formation" => $prospects_plusieurs_formation );
        }else {
            return  null;
        }
        
    }

    public function ApprenantsTab($date_du_jour,$code_periode_actuel,$code_periode_precedente, $frequente_tab = null)
    {

         //requete avec cache pour test affichage
        $api_data_apprenants = Cache::get('api_data_apprenants');
        if (empty($api_data_apprenants)) {
            $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);
            Cache::put('api_data_apprenants', $api_data_apprenants, 32000);
        }
        
        $api_data_apprenants_precedent = Cache::get('api_data_apprenants_precedent');
        if (empty($api_data_apprenants_precedent)) {
            $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente);
            Cache::put('api_data_apprenants_precedent', $api_data_apprenants_precedent, 32000);
        }
                                
        $api_data_frequentes_precedent = Cache::get('api_data_frequentes_precedent');
        if (empty($api_data_frequentes_precedent)) {
            $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente);
            Cache::put('api_data_frequentes_precedent', $api_data_frequentes_precedent, 32000);
        }    
        
        /*         $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);
        $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente);           
        $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente);  */  
        

        // Construction de la table de fraquantation de l'année precedente
        foreach ($api_data_frequentes_precedent as $frequente) {
                
            $frequente_tab_precedent[$frequente["codeApprenant"]] = $frequente["codeApprenant"];
        
        } 

        // Construction de la table apprenants precendent contenant les apprenants 
        // ayant frequante l'etablisement l'annee precedente et le nom de la formation 
        foreach ($api_data_apprenants_precedent as $apprenant) {  

            // on verifie que l'apprenant a bien frequante l'etablisement l'annee precedente
            if (!empty($frequente_tab_precedent[$apprenant["codeApprenant"]])) {

                //on parcourt ces inscriptions
                for ($i=0; $i < count($apprenant["inscriptions"]) ; $i++) { 
                    //on prend l'inscription en cours / correspond bien a l'inscription en cours lors de la periode choisi
                    if ($apprenant["inscriptions"][$i]["isInscriptionEnCours"] == 1) {
                        // table apprenants precendent contenant les apprenants et le nom de la formation
                        $apprenants_tab_precedent[$apprenant["codeApprenant"]] = $apprenant["inscriptions"][$i]["formation"]["nomFormation"];

                    }
                }
                
            }
                        
        }
          
        
        $api_data_contrats = $this->ApiContrats($code_periode_actuel);     

        // Creation de la table contrat depuis la requete api
        foreach ($api_data_contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            //$date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );

            // on garde les contrats qui non pas de date de resiliation et qui sont relier a une entreprise
            // les contrats qui nom pas d'entreprise corresponde a un contrat intermedaire suite a une rupture en attandant que l'apprenant trouve une nouvel entreprise
            if (empty($contrat["dateResiliation"]) && !empty($contrat["codeEntreprise"])) {
      
                if ($date_fin > $date_du_jour) {

                    $contrats_tab[$contrat["codeApprenant"]] = array(
                        "codeEntreprise" =>$contrat["codeEntreprise"],
                        "codeContrat" => $contrat["codeContrat"],
                        "dateDebContrat" => $contrat["dateDebContrat"]
                    );
                
                }
            }

        }

        //Tableau entreprise mis en cache car indepandant de la date et de la periode
        $entreprises_tab = Cache::get('entreprises_tab');
        if (empty($entreprises_tab)) {

            //requete api
            $api_data_entreprises = $this->ApiEntreprises();

            // Tableau avec code entreprise en clef et le nom en valeur 
            foreach ($api_data_entreprises as $entreprise) {
                $entreprises_tab[$entreprise["codeEntreprise"]] = $entreprise["nomEntreprise"];
            }

            Cache::put('entreprises_tab', $entreprises_tab, 32000);
        }

 

        // tableau apprenant complete 
        foreach ($api_data_apprenants as $apprenant) {
            
            //si l'apprenant et dans la table de frequentation
            if (!empty($frequente_tab[$apprenant["codeApprenant"]])) {     
                
                //tout apprenant et nouveau sauf s'il est dans la table apprenants_tab_precedent et que son nom de formation est le meme
                $nouveau = 1;
                if (!empty($apprenants_tab_precedent[$apprenant["codeApprenant"]])) {
 
                    $nomfomation = $apprenant["inscriptions"][0]["formation"]["nomFormation"];
                    $nomfomation_precedent = $apprenants_tab_precedent[$apprenant["codeApprenant"]];
                    if ($nomfomation == $nomfomation_precedent) {
                        $nouveau = 0;
                    }           
                }

                //on parcourt ces inscriptions
                for ($i=0; $i < count($apprenant["inscriptions"]) ; $i++) { 
                    //on prend l'inscription en cours
                    if ($apprenant["inscriptions"][$i]["isInscriptionEnCours"] == 1) {
                        $apprenants_tab[$apprenant["codeApprenant"]] = array(
                            "nomApprenant" => $apprenant["nomApprenant"],
                            "prenomApprenant" => $apprenant["prenomApprenant"],
                            "nouveau" => $nouveau,
                            "nomStatut" => $apprenant["inscriptions"][$i]["situation"]["nomStatut"],
                            "nomAnnee" => $apprenant["inscriptions"][$i]["situation"]["nomAnnee"],
                            "nomFormation" => $apprenant["inscriptions"][$i]["formation"]["nomFormation"],
                            "nomSecteurActivite" => $apprenant["inscriptions"][$i]["formation"]["nomSecteurActivite"],
                        );
                    }
                }

                // si il a un contrat on rajoute les informations du contrat
                if (!empty($contrats_tab[$apprenant["codeApprenant"]])) {
                
                    $code_entreprise = $contrats_tab[$apprenant["codeApprenant"]]["codeEntreprise"];

                    $apprenants_tab[$apprenant["codeApprenant"]] += array(
                        "dateDebContrat" => $contrats_tab[$apprenant["codeApprenant"]]["dateDebContrat"],
                        "nomEntreprise" => $entreprises_tab[ $code_entreprise ]
                        //"nomEntreprise" => $entreprises_tab[ $contrat[$apprenant["codeApprenant"]]["codeEntreprise"] ]["nomEntreprise"]
                    );

                }

            }
        }

        //si la table apprenant est null
        if (isset($apprenants_tab)) {
            return  $apprenants_tab;
        }else {
            return  null;
        } 
       
    }

    public function PrevisDataBase(Request $request)
    {
        
        $periode = $request->get('periode');
        $date = $request->get('date');
        //$date = '2022-07-08';
       // echo('indexSaveDatabase');
       //dd($request->input());
        //exit;
        //$previ = new \App\Models\Previ;
        foreach ($request->input() as $key => $value) {
            if (is_numeric($key)) {      
                if(is_numeric($value)) {      
                    $previ = \App\Models\Previ::updateOrCreate(
                        ['idFormation' => $key, 'periode' => $periode],
                        ['previ' => $value ]
                    );
                }else {
                    return redirect()->route('relation_entreprise_index', ['date' => $date])->with('flash_message', 'Erreur les champs doivent etre des nombres')
                    ->with('flash_type', 'alert-danger');
                }
            }
        }
                //return redirect(route('relation_entreprise_index')."?date=$date")->with('flash_message', 'Erreur les champs doivent etre des nombres')
        //->with('flash_type', 'alert-danger');

        /* Session::flash('flash_message', '<b>Well done!</b> You successfully logged in to this website.');
        Session::flash('flash_type', 'alert-success'); */
       return redirect()->route('relation_entreprise_index', ['date' => $date])->with('flash_message', 'Previsionel enregitrer')
                                                    ->with('flash_type', 'alert-success');

    }

    public function Erreur($tableau_complet)
    {

        $formations = \App\Models\Formations::all();
        
        $liste_annee_null = [];
        $liste_annee_mauvaise = [];
        foreach ($tableau_complet as $codeApprenant => $individu) {

            $pas_bonne_annee = True;
            foreach ($formations as $formation) {
                if($individu['nomFormation'] == $formation["nomFormation"]  && $individu["nomAnnee"] == $formation["nomAnnee"]){
                    $pas_bonne_annee = False;
                }
            }

            if ($pas_bonne_annee) {
                if(empty($individu["nomAnnee"])){
                    array_push($liste_annee_null, $codeApprenant);
                    
                }  else {
                    if ($individu["nomFormation"] !='ERASMUS POST-APPRENTISSAGE') {
                        array_push($liste_annee_mauvaise, $codeApprenant);
                    }
                    
                }
            }



        }

        dump( $tableau_complet);
        dump( $liste_annee_mauvaise);
        dd( $liste_annee_null);
        

        return array('liste_annee_mauvaise' => $liste_annee_mauvaise,
                    'liste_annee_null' => $liste_annee_null
                    );

    }





    public function test($code_periode_actuel, $date_annee_precedente, $date_du_jour, $frequente_tab)
    {
        //####################################################
        //#################################################################
        $tab1 = array (
            776224 => 
            array (
              'nomApprenant' => 'ABDUL',
              'prenomApprenant' => 'Rakib',
              'dateCreation' => '20/04/2021',
              'nouveau' => 0,
              'nomStatut' => 'Apprenti',
              'nomAnnee' => '2ème année',
              'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'dateDebContrat' => '25/08/2021',
              'nomEntreprise' => 'CONSEIL DEPARTEMENTAL D\'INDRE ET LOIRE',
            ),
            887776 => 
            array (
              'nomApprenant' => 'ABEDRABA',
              'prenomApprenant' => 'Shourog',
              'dateCreation' => '25/08/2021',
              'nouveau' => 0,
              'nomStatut' => 'Apprenti',
              'nomAnnee' => '2ème année',
              'nomFormation' => 'BTS MCO APP',
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'dateDebContrat' => '06/09/2021',
              'nomEntreprise' => 'GREENSUB',
            )
            );

            $tab2 = array (
                776224 => 
                array (
                  'nomApprenant' => 'ABDUL',
                  'prenomApprenant' => 'Rakib',
                  'dateCreation' => '20/04/2021',
                  'nomStatut' => 'Apprenti',
                  'nomAnnee' => '1ème année',
                  'nomFormation' => 'CAP PRODUCTION ',
                  'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',

                ),
                888888 => 
                array (
                  'nomApprenant' => 'ABEDRABA',
                  'prenomApprenant' => 'Shourog',
                  'dateCreation' => '25/08/2021',
                  'nouveau' => 0,
                  'nomStatut' => 'Apprenti',
                  'nomAnnee' => '1ème année',
                  'nomFormation' => 'BTS MCO APP',
                  'nomSecteurActivite' => 'COMMERCE - VENTE',
                  'dateDebContrat' => '06/09/2021',
                  'nomEntreprise' => 'GREENSUB',
                )
                );
                /* 
                dump($tab1);
                dump($tab2);
        $tab = array_merge($tab1, $tab2);
        $tab = $tab2 +$tab1;
        dd($tab); */


       


        $date_debut_prospect = $date_annee_precedente->format('d-m-Y');
        $date_fin_prospect = $date_du_jour->format('d-m-Y');
        $prospects_tab = $this->ProspectsTab($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab);

        //dd($prospects_tab);

        $api_data_contrats = $this->ApiContrats($code_periode_actuel);
     
        $erreur = 0;
        foreach ($api_data_contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            //$date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );

            if (empty($contrat["dateResiliation"]) && !empty($contrat["codeEntreprise"])) {
                if ($date_fin > $date_du_jour) {

                $contrats_tab[$contrat["codeApprenant"]] = array(
                    "codeEntreprise" =>$contrat["codeEntreprise"],
                    "codeContrat" => $contrat["codeContrat"],
                    "dateDebContrat" => $contrat["dateDebContrat"]
                );
                
                }
            }else {
                $date_fin = date_create_from_format('d/m/Y', $contrat["dateResiliation"] );
                if ($date_fin > $date_du_jour) {

                    $erreur++;
                    //dump($contrat);
                }
                    
            }
        }

        echo("prospects_tab");
        //dump($prospects_tab);

        $count= 0;
        $liste_prospect_contrat = [];
        foreach ($prospects_tab as $codeApprenant => $prospect) {
            if (!empty($contrats_tab[$codeApprenant])) {
                  $count++;
                  array_push($liste_prospect_contrat, $codeApprenant);
            }
        } 

        echo("nombre de prsopect avec contrat");
        dump($count);

        $api_data_apprenants = Cache::get('api_data_apprenants');
        $api_data_prospects = Cache::get('api_data_prospects');
        $count= 0;
        foreach ($liste_prospect_contrat as $codeApprenant) {
            
            foreach ($api_data_apprenants as $apprenant) {          
                if ($apprenant["codeApprenant"] == $codeApprenant) {
                    $count++; 
                    echo("apprenant: $codeApprenant");   
                    //dump($apprenant);   
                }   
            }

            foreach ($api_data_prospects as $prospect) {
    
                if ($prospect["codeApprenant"] ==  $codeApprenant ){  
                    echo("prospect: $codeApprenant");          
                    //dump($prospect);
                }
            }

        }
        echo("nombre de prospect avec contrat aussi appreant");
        dump($count);


        $this->testfrequantation($prospects_tab);





        exit;
    
        //exit;
        //#########################################################################
        //code contrat recu prosopect date debut formation ?
        $code=600532; 
        /*      $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/600532";
        $test = $this->ApiCall($url);
        dd($test); */
        //code formation voulu 524017
        $api_data_prospects = Cache::get('api_data_prospects');

        foreach ($api_data_prospects as $prospect) {

            if ($prospect["codeApprenant"] ==  $code ){  
                echo('prospect');          
                dump($prospect);
            }
        }

        $api_data_frequentes = Cache::get('api_data_frequentes');
        //dump($api_data_frequentes);
        $count = 0;
        foreach ($api_data_frequentes as $key => $value) {
            if ($value["codeApprenant"] == $code) {
                echo("apprenand dans la table freq $code");
                dump($value);           
            }
            if ($value["codeGroupe"] == 846516) {
                //dump($value); 
                $count++;          
            }
        }
        echo("nb apprenant tab frequentatation avec codegroupe");
        dump($count);




       

        //code classe terminal dans frequentation
        /*    $code = ;
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/1068605";
        $test = $this->ApiCall($url);
        dd($test); */
        $api_data_groupes = $this->ApiGroupes($code_periode_actuel);//cache
        $count = 0;
        foreach ($api_data_groupes as $key => $value) {
            if ($value["codeFormation"] == 524017) {
                //echo("groupe");
                //dump($value);
                //524017 BAC PRO COMMERCE VENTE OPT A
                //846703 1er annee/second
                //846516 2eme annee/premiere
                //846729 terminal
                $count++;
            }
        }
        //dump($count);
        //dump($api_data_groupes);
        
        //code classe second dans frequentation
        $code = 989497;

        $api_data_frequentes = Cache::get('api_data_frequentes');
        //dump($api_data_frequentes);
        $count = 0;
        foreach ($api_data_frequentes as $key => $value) {
            if ($value["codeApprenant"] == $code) {
                echo("apprenand dans la table freq $code");
                dump($value);           
            }
            if ($value["codeGroupe"] == 846516) {
                dump($value); 
                $count++;          
            }
        }
        echo("nb apprenant tab frequentatation avec codegroupe");
        dump($count);

        //code avec plusieur formation
    
        //$code=843844;
        //$code=753150;

        //$code=593154;
        //$code=763676;
        //dump($frequente_tab[$code]);
        //dump($code);
        $api_data_apprenants = Cache::get('api_data_apprenants');
        foreach ($api_data_apprenants as $apprenant) {          
            if ($apprenant["codeApprenant"] == $code) { 
                echo("apprenant: $code");   
                dump($apprenant);
            }else{
                //echo("pas dans la liste aprenant");
            }
        }   


        $api_data_prospects = Cache::get('api_data_prospects');

        foreach ($api_data_prospects as $prospect) {

            if ($prospect["codeApprenant"] ==  $code ){  
                echo("prospect: $code");          
                dump($prospect);
            }
        }

        $api_data_prospect_evenement = $this->ApiProspectsEvenement($codeTypeEvt=4, 149, $date_debut_prospect, $date_fin_prospect, $evtClotures=0); 
        //dump($api_data_prospect_evenement);
        foreach ($api_data_prospect_evenement as $prospect) {

            if ($prospect["codeApprenant"] ==  $code ){  
                echo("prsopect venant de la table prospect evenement");          
                dump($prospect);
            }
        }
          


        //564049
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/564049";
        //$test = $this->ApiCall($url);
        //dd($test);

        $code=880416;
        
        $code=508715;
        $code=801141;
        $code= 0;
        //dump($prospects_tab_recu[$code]);
        //dump($prospects_tab_reception[$code]);
        //dump($prospects_tab_envoi[$code]); 
        echo("test code");
        $api_data_prospects = $this->ApiProspects($code_periode_actuel);
        $api_data_prospects = Cache::get('api_data_prospects');
        foreach ($api_data_prospects as $prospect) {

            if ($prospect["codeApprenant"] ==  $code ){
                $t= count($prospect["evenementsRacines"]);
                echo("prospect evenement racine $t");              
                dump($prospect);

            }

        }



        $prospects_tab_recu = Cache::get('prospects_tab_recu');
        $prospects_tab_reception = Cache::get('prospects_tab_reception');
        $prospects_tab_envoi = Cache::get('prospects_tab_envoi');
 
        foreach ($api_data_prospects as $codeApprenant => $prospect) {

            $code_app = $prospect["codeApprenant"];
            if (!empty($prospects_tab_envoi[$code_app]) || !empty($prospects_tab_recu[$code_app]) || !empty($prospects_tab_reception[$code_app]) ) {
               
                // un prospect ne peu pas etre en cours de formation
                if (empty($frequente_tab[$code_app])) {
                   
                
                    //un prospect peu avoir plusieurs evenement racine, on prend le dernier qui correspond au dernier en date
                    $nombre_evenement_racine = count($prospect["evenementsRacines"]);            
                    $dernier_evenement_racine = $prospect["evenementsRacines"][$nombre_evenement_racine - 1]; 

                    // si un evenement choisi c'est passe sur la periode mais n'est pas le dernier
                    $codeEtape = $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"];
                    if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {
                
                        if(!empty($dernier_evenement_racine["formationsSouhaitees"][1])){
                            $prospects_tab_test[$prospect["codeApprenant"]] = array(
                                "codeEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"],
                                "nomEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["nomEtapeEvenement"],
                                "nomFormation" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"],
                                "nomFormation2" => $dernier_evenement_racine["formationsSouhaitees"][1]["nomFormation"],
                                "nomAnnee" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomAnnee"],
                                "nomStatut" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomStatut"],
                                "nomApprenant" => $prospect["nomApprenant"],
                                "prenomApprenant" => $prospect["prenomApprenant"]
                            );
                        }
                    }
                }
            }

                     
        }

        dd($prospects_tab_test);
 
        $api_data_apprenants = Cache::get('api_data_apprenants');
        //$api_data_apprenants = $this->ApiApprenants(15);
        //$api_data_apprenants = Cache::get('api_data_apprenants_precedent');
        foreach ($api_data_apprenants as $apprenant) {
            
            //si l'apprenant et dans la table prsopect
            if (!empty( $prospects_tab_test[$apprenant["codeApprenant"]])) {    


                $apprenants_tab[$apprenant["codeApprenant"]] = array(
                    "nomApprenant" => $apprenant["nomApprenant"],
                    "prenomApprenant" => $apprenant["prenomApprenant"],
                    "dateCreation" => $apprenant["dateCreation"],
                );

                for ($i=0; $i < count($apprenant["inscriptions"]) ; $i++) { 
                    if ($apprenant["inscriptions"][$i]["isInscriptionEnCours"] == 1) {
                        $apprenants_tab[$apprenant["codeApprenant"]] += array(
                            "nomStatut" => $apprenant["inscriptions"][$i]["situation"]["nomStatut"],
                            "nomAnnee" => $apprenant["inscriptions"][$i]["situation"]["nomAnnee"],
                            "nomFormation" => $apprenant["inscriptions"][$i]["formation"]["nomFormation"],
                            "nomSecteurActivite" => $apprenant["inscriptions"][$i]["formation"]["nomSecteurActivite"],
                        );
                    }
                }


            }
        }
        dump($prospects_tab_test);
        dump($apprenants_tab); 

  
        echo("fin test");
        exit; 
        //####################################################
        //#########################################################################
    }


    public function testCommun($apprenants_tab, $prospects_tab)
    {

        $liste_commun =[];
        $liste_commun_comp=[];
        foreach ($apprenants_tab as $codeApprenant => $apprenants) {
            if(!empty($prospects_tab[$codeApprenant])){
                array_push($liste_commun, $codeApprenant);
                array_push($liste_commun_comp,
                        array(
                            $codeApprenant,
                            'apprenant' => $apprenants, 
                            'prospect' => $prospects_tab[$codeApprenant]
                        ) 
                );

            }

        }
        echo('apprenant a la fois  dans prospects_tab et apprenants_tab ');
        dump($liste_commun_comp);
        exit;


        $api_data_apprenants = Cache::get('api_data_apprenants');
        $api_data_prospects = Cache::get('api_data_prospects');

        foreach ($liste_commun as $codeApprenant) {
            
            foreach ($api_data_apprenants as $apprenant) {          
                if ($apprenant["codeApprenant"] == $codeApprenant) { 
                    echo("apprenant: $codeApprenant");   
                    dump($apprenant);   
                }   
            }

            foreach ($api_data_prospects as $prospect) {
    
                if ($prospect["codeApprenant"] ==  $codeApprenant ){  
                    echo("prospect: $codeApprenant");          
                    dump($prospect);
                }
            }

            echo("##############################");

        }
        echo("FIN RESULTAT COMMUN");
        exit;
    }

    public function testfrequantation($prospects_tab)
    {

        $api_data_apprenants = Cache::get('api_data_apprenants');
        $api_data_prospects = Cache::get('api_data_prospects');
        $api_data_frequentes = Cache::get('api_data_frequentes');

        $api_data_apprenants = $this->ApiApprenants(11);
        $api_data_frequentes = $this->ApiFrequentes(11);


        foreach ($api_data_frequentes as $frequente) {
                $frequente_tab_netoyer[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );
        }

        $count = 0;
  
        $liste_app_pas_freq = [];
        foreach ($api_data_apprenants as $apprenant) {
                    
            if (empty($frequente_tab_netoyer[$apprenant["codeApprenant"]])) {     

                array_push($liste_app_pas_freq, $apprenant);
                dump($apprenant);
                //si l'apprenant et dans la table de frequentation
                if (!empty($prospects_tab[$apprenant["codeApprenant"]])) {     
                    $count++;
                }
   
            }

        }
        echo("dans la table apprenant mais pas dans la table frequente");
        dump($liste_app_pas_freq);
        echo("dans la table prospect et apprenant pas dans la table frequente");
        dump($count);
        

        $count = 0;
        $liste_commun = [];
        foreach ($prospects_tab as $codeApprenant => $prospect) {
            if (!empty($frequente_tab_netoyer[$codeApprenant])) {     
               
                $count++;
                array_push($liste_commun, $codeApprenant);
   
            }
        }
        
        echo("dans la table prospect et dans la table frequente");
        dump($count);
        dump($liste_commun);

        exit;

        foreach ($liste_commun as $codeApprenant) {
            
            foreach ($api_data_apprenants as $apprenant) {          
                if ($apprenant["codeApprenant"] == $codeApprenant) { 
                    echo("apprenant: $codeApprenant");   
                    dump($apprenant);   
                }   
            }

            foreach ($api_data_prospects as $prospect) {
    
                if ($prospect["codeApprenant"] ==  $codeApprenant ){  
                    echo("prospect: $codeApprenant");          
                    dump($prospect);
                }
            }
        }


        exit;
    }

    public function frequentUnique()
    {
        
        $api_data_apprenants = Cache::get('api_data_apprenants');
        $api_data_prospects = Cache::get('api_data_prospects');
        $api_data_frequentes = Cache::get('api_data_frequentes');

        $count = 0;
        $frequente_tab=[];
        $freq2=[];
        foreach ($api_data_frequentes as $frequente) {
            Array_push($freq2, $frequente["codeApprenant"] );

                $frequente_tab[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );
                $count++;
                
                if ($frequente["codeApprenant"] == 530254) {
                    dump($frequente);
                }
            
        }

        dump($count);
        //dump($frequente_tab);
        dump($freq2);
        dump(array_count_values($freq2));

        $unique = array_map("unserialize", array_unique(array_map("serialize", $frequente_tab)));
         //dump($unique);

        $codeApprenant = 530254;
        foreach ($api_data_apprenants as $apprenant) {          
            if ($apprenant["codeApprenant"] == $codeApprenant) { 
                echo("apprenant: $codeApprenant");   
                dump($apprenant);   
            }   
        }
       
        exit;

    }

    public function prospectstest($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab= null, $apprenants_tab)
    {
        

        //$api_data_prospects = Cache::get('api_data_prospects');
        if (empty($api_data_prospects)) {

            $api_data_prospects = $this->ApiProspects(11);
            Cache::put('api_data_prospects', $api_data_prospects, 32000);
        }
        dump($api_data_prospects);

        $api_data_prospects_futur = Cache::get('api_data_prospects_futur');
        if (empty($api_data_prospects_futur)) {

            $api_data_prospects_futur = $this->ApiProspects(15);
            Cache::put('api_data_prospects_futur', $api_data_prospects_futur, 32000);
        }
        dump($api_data_prospects_futur);

        #################################################################"
        $prospects_tab_recu = Cache::get('prospects_tab_recu');
        if (empty($prospects_tab_recu)) {

            $prospects_tab_recu = $this->ProspectEvenement($codeEvenement=8, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_recu', $prospects_tab_recu, 32000);
        }

        $prospects_tab_reception = Cache::get('prospects_tab_reception');
        if (empty($prospects_tab_reception)) {

            $prospects_tab_reception = $this->ProspectEvenement($codeEvenement=151, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_reception', $prospects_tab_reception, 32000);
        }

        $prospects_tab_envoi = Cache::get('prospects_tab_envoi');
        if (empty($prospects_tab_envoi)) {

            $prospects_tab_envoi = $this->ProspectEvenement($codeEvenement=149, $date_debut_prospect, $date_fin_prospect );
            Cache::put('prospects_tab_envoi', $prospects_tab_envoi, 32000);
        }


        ###########################################"""
        $prospects_tab_recu_test = Cache::get('prospects_tab_recu_test');
        if (empty($prospects_tab_recu_test)) {
            $prospects_tab_recu_test = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=8, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('prospects_tab_recu_test', $prospects_tab_recu_test, 32000);
        }

        $prospects_tab_reception_test = Cache::get('prospects_tab_reception_test');
        if (empty($prospects_tab_reception_test)) {

            $prospects_tab_reception_test = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=151, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('prospects_tab_reception_test', $prospects_tab_reception_test, 32000);
        }

        $prospects_tab_envoi_test = Cache::get('prospects_tab_envoi_test');
        if (empty($prospects_tab_envoi_test)) {

            $prospects_tab_envoi_test = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=149, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('prospects_tab_envoi_test', $prospects_tab_envoi_test, 32000);
        }



        
        foreach ($prospects_tab_envoi as $key => $value) {
            if (!empty($prospects_tab_reception[$key])) {
                echo("prospect envoi evenement commun reception");
                dump($key);
            }
            if (!empty($prospects_tab_recu[$key])) {
                echo("prospect envoi evenement commun recu");
                dump($key);
            }
        }

        dump($prospects_tab_envoi_test);
        dump($prospects_tab_reception_test);
        dump($prospects_tab_recu_test);
        //exit;
        $prospect_evenement = array_merge($prospects_tab_envoi_test , $prospects_tab_reception_test, $prospects_tab_recu_test ,);
        echo("prospect_evenement");
        dump($prospect_evenement);

        foreach ($api_data_prospects as $codeApprenant => $prospect) {
            $code_app = $prospect["codeApprenant"];
            if (!empty($prospects_tab_envoi[$code_app]) || !empty($prospects_tab_recu[$code_app]) || !empty($prospects_tab_reception[$code_app]) ) {
                $prospects_tab_actu[$prospect["codeApprenant"]] = $prospect;
            }
        }
        echo("prospects_tab_actu");
        dump($prospects_tab_actu);
        
        foreach ($api_data_prospects_futur as $codeApprenant => $prospect) {
            $code_app = $prospect["codeApprenant"];
            if (!empty($prospects_tab_envoi[$code_app]) || !empty($prospects_tab_recu[$code_app]) || !empty($prospects_tab_reception[$code_app]) ) {
                $prospects_tab_futur[$prospect["codeApprenant"]] = $prospect;
            }
        }
        echo("prospects_tab_futur");
        dump($prospects_tab_futur);

        foreach ($prospect_evenement as $key => $prospect) {
             if(empty($prospects_tab_futur[$prospect["codeApprenant"]])){
                $prospects_tab_exclu[$prospect["codeApprenant"]] = $prospect;
            } 
        }
        echo("prospects_tab_exclu prospect dans les requete evenement mais pas prospect sur la requete periode");
        dump($prospects_tab_exclu);
        
        echo("prospects_tab_exclu prospect commun tab apprenant");
        $this->testCommun($apprenants_tab, $prospects_tab_actu);

        exit;



        foreach ($api_data_prospects as $prospect) {
            $api_data_prospects_test[$prospect["codeApprenant"]] = array(
                "codeApprenant" => $prospect["codeApprenant"]
            );
            if($prospect["codeApprenant"] == 508715){
                echo('prospect api');
                dump($prospect);
            }
        }


        foreach ($prospects_tab_envoi as $key => $value) {
            if($key == 508715){
                echo('prospect envoi');
                dd($value);
            }
            /* if(empty($api_data_prospects_test[$key])){
                dd($value);
            } */
        }
        

        foreach ($api_data_prospects as $codeApprenant => $prospect) {

            $code_app = $prospect["codeApprenant"];
            if (!empty($prospects_tab_envoi[$code_app]) || !empty($prospects_tab_recu[$code_app]) || !empty($prospects_tab_reception[$code_app]) ) {
               
                // un prospect ne peu pas etre en cours de formation
                //if (empty($frequente_tab[$code_app])) {
                   
                
                    //un prospect peu avoir plusieurs evenement racine, on prend le dernier qui correspond au dernier en date
                    $nombre_evenement_racine = count($prospect["evenementsRacines"]);  
                    

                    $dernier_evenement_racine = $prospect["evenementsRacines"][$nombre_evenement_racine - 1]; 

                    // si un evenement choisi c'est passe sur la periode mais n'est pas le dernier
                    $codeEtape = $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"];
                    if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {
                
                        //Construction du tableau prsopects
                        //Attention certain prospect on plusieur formation souhaite pour l'instant on prend la premiere
                        $prospects_tab[$prospect["codeApprenant"]] = array(
                            "nomApprenant" => $prospect["nomApprenant"],
                            "prenomApprenant" => $prospect["prenomApprenant"],
                            "nomFormation" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"],
                            "nomAnnee" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomAnnee"],
                            "nomStatut" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomStatut"],
                            "codeEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"],
                            "nomEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["nomEtapeEvenement"],

                        );

                        $nombre_formation = count($dernier_evenement_racine["formationsSouhaitees"]);
                        if ( $nombre_formation > 1) {
                            $prospects_plusieurs_formation[$prospect["codeApprenant"]] = $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"];
                        /*                            
                            for ($i=0; $i < $nombre_formation ; $i++) {
                                $prospects_plusieurs_formation[$prospect["codeApprenant"]] += array(

                                    "nomFormation$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomFormation"],
                                    "nomAnnee$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomAnnee"],

                                );
                            } */
                            
                        }        

                    }else{
                        if ($nombre_evenement_racine>1) {
                            dump($prospect);
                        }
                    }
                //}
            }
    
        }
    }


}//fin class
