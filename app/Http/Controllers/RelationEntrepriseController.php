<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\ApiRequestTrait;

//pour utilise requete api
use Illuminate\Support\Facades\Http;
//mise en cache
use Illuminate\Support\Facades\Cache;

class RelationEntrepriseController extends Controller
{
    
    use ApiRequestTrait;

    public $tab;

    public function index(Request $request)
    {   

        //vider tous le cache
        //Cache::flush();
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '512M' );
        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        
        // Recuperation de la table periode et trouver la periode qui correspond a la date voulu (date du jour par defaut) ainsi que la periode n-1
        //##############################################################"
        
        //On recupere la date s'il y en a une
        $date = $request->get('date');


        if(empty($date)){
            echo("ttttt dateempty tttttt");
            $date_vide = true;
            $date = date("Y-m-d");
            $date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );
            $date_annee_precedente = date_create_from_format('d/m/Y', date("d/m/Y") )->modify('-1 year');

        }else {

            $date_du_jour = date_create_from_format('Y-m-d', $date ) ;
            $date_annee_precedente = date_create_from_format('Y-m-d', $date )->modify('-1 year');
        }

        //Requete api
        $api_data_periodes = $this->ApiPeriodes();

        foreach ($api_data_periodes as $periode) {

            $dateDeb = date_create_from_format('d/m/Y', $periode["dateDeb"] );
            $dateFin = date_create_from_format('d/m/Y', $periode["dateFin"] );

            if ($dateDeb <= $date_du_jour && $date_du_jour < $dateFin) {
                $code_periode_actuel = $periode["codePeriode"];
                $periode_actuel = $periode["nomPeriode"];
            }

            //preiode precedentes
            if ($dateDeb <= $date_annee_precedente && $date_annee_precedente < $dateFin) {
                $code_periode_precedente = $periode["codePeriode"];
            }
            
        }


        if(!isset($code_periode_actuel)){
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond a une periode qui n'existe pas encore")
            ->with('flash_type', 'alert-danger');
        }

        $previs = \App\Models\Previ::where('periode', $periode_actuel)->get();

        //$date_vide = null;
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

       // $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);

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
            //if( (empty($frequente["dateFin"]) || $date_du_jour < $date_fin) && ($date_du_jour > $date_deb)) {
                
                $frequente_tab[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );
                $count++;
            //}
        }

        //##############################################################
        //#############################################################
        //$this->test($code_periode_actuel, $date_annee_precedente, $date_du_jour, $frequente_tab);
        //exit;
        //#############################################################
        //#############################################################

/* 
        $date_tab =0;
        $date_tab = $request->session()->get('date_flash');
  
        if($date == $date_tab){
            
            echo('******************date = date_flash *****************');
            $final_tab = $request->session()->get('final_tab_flash');
            $total_tab = $request->session()->get('total_tab_flash');
            $tableau_complet = $request->session()->get('tableau_complet_flash'); 

            return view('relationentreprise')
            ->with(compact('final_tab'))
            ->with(compact('total_tab'))
            ->with(compact('tableau_complet'))
            ->with(compact('previs'))
            ->with(compact('date'))
            ->with(compact('periode_actuel'));
        }  */

        
        // Fin calcul Periode


      
        $apprenants_tab = $this->ApprenantsTab($date_du_jour,$code_periode_actuel,$code_periode_precedente, $frequente_tab);



        //##Recupere la table des prospects voulu##
        $date_debut_prospect = $date_annee_precedente->format('d-m-Y');
        $date_fin_prospect = $date_du_jour->format('d-m-Y');

        $prospects_tab = $this->ProspectsTab($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab);
        //##



/*         foreach ($prospects_tab as $key => $prospect) {
            if (empty($prospect['nomAnnee'])) {
                $null_tab[$key] = array(
                    "codeApprenant" =>$key,
                );
            }
        }
        dd($null_tab); */

        if (isset($prospects_tab) && isset($apprenants_tab)) {
            $tableau_complet = array_merge($prospects_tab, $apprenants_tab);
        }elseif (isset($apprenants_tab)) {
            $tableau_complet = $apprenants_tab;
        }elseif (isset($prospects_tab)){
            $tableau_complet = $prospects_tab;
        }else{
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond a une periode qui existe mais il n'y a encore ni prospects ni apprenant")
            ->with('flash_type', 'alert-danger');  
        }

        //$tableau_complet = array_merge($prospects_tab, $apprenants_tab);
        //$tableau_complet_cache = Cache::get('tableau_complet_cache');
        if (empty($tableau_complet_cache)) {
            Cache::put('tableau_complet_cache', $tableau_complet, 360);
        }

       
        
        $liste_tableau = $this->ConstructionTableauFinal($tableau_complet);
        $final_tab = $liste_tableau['final_tab'];
        $total_tab = $liste_tableau['total_tab'];
        
        //$d_microtime = microtime(true);
        //$d_memory = memory_get_usage(true);

        // mise en cache tableau complet, final_tab et total_tab dans le cas ou la date est vide et donc on prend la date du jour
        if(isset($date_vide)){
            echo('sesesese  set date vide seseseeseees');
            Cache::put('final_tab_date_vide', $final_tab, 32000);
            Cache::put('total_tab_date_vide', $total_tab, 32000);
            Cache::put('tableau_complet_date_vide', $tableau_complet, 32000);
        }



        echo('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $request->session()->put('tableau_complet_flash', $tableau_complet);
        $request->session()->put('date_flash', $date);
        $request->session()->put('final_tab_flash', $final_tab);
        $request->session()->put('total_tab_flash', $total_tab);

        //var_dump($this->tab);
        //Session::set('tableau_complet', $tableau_complet);

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo("temps execution : {$time}, Memoire utlisé : {$memory}"); 

        return view('relationentreprise')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('tableau_complet'))
                ->with(compact('previs'))
                ->with(compact('date'))
                ->with(compact('periode_actuel'));

    }


    public function ConstructionTableauFinal($tableau_complet)
    {

        $formations = \App\Models\Formations::all();
            
        $final_tab = [];
        $precontrat_total=0;
        $reception_contrat_total=0;
        $contrat_recu_total=0;
        $nouveau_total=0;
        $ancient_total=0;
        $count_total=0;

        foreach ($formations as $formation) {

            $precontrat=0;
            $reception_contrat=0;
            $contrat_recu=0;
            $nouveau=0;
            $ancient=0;
            $count=0;

            foreach ($tableau_complet as $individu) {

                if($individu['nomFormation'] == $formation["nomFormation"]  && $individu["nomAnnee"] == $formation["nomAnnee"]){
                
                    if (!empty($individu['codeEtapeEvenement'])) {

                        if($individu['codeEtapeEvenement'] == 151){
                            $precontrat++;
                            $precontrat_total++;
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
                "precontrat" => $precontrat,
                "receptioncontrat" => $reception_contrat,
                "contratrecu" => $contrat_recu,
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
                        "precontrat" => $precontrat_total,
                        "receptioncontrat" => $reception_contrat_total,
                        "contratrecu" => $contrat_recu_total,
                        "nouveau" => $nouveau_total,
                        "ancient" => $ancient_total,
                        "total" => $count_total,
        );
        

        return array("final_tab" => $final_tab, "total_tab" => $total_tab );
    }

    protected function ProspectEvenement($codeEvenement, $date_debut_prospect, $date_fin_prospect )
    {

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        // codeTypeEvt trouver avec la table typeEvenement https://citeformations.ymag.cloud/index.php/r/v1/types-evenement
        $api_data_prospect_evenement = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);  

        foreach ($api_data_prospect_evenement as $prospect) {
            $tab_data_prospect_evenement[$prospect["codeApprenant"]] = array(
                "codeApprenant" => $prospect["codeApprenant"]
            );
        }

        if (isset($tab_data_prospect_evenement)) {
            return $tab_data_prospect_evenement;
        }else {
            return  null;
        }
    }

    protected function ProspectsTab($code_periode_actuel, $date_debut_prospect, $date_fin_prospect, $frequente_tab= null)
    {
        $api_data_prospects = $this->ApiProspects($code_periode_actuel);

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
                
                        //Construction du tableau prsopects
                        //Attention certain prospect on plusieur formation souhaite pour l'instant on prend la premiere
                        $prospects_tab[$prospect["codeApprenant"]] = array(
                            "codeEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"],
                            "nomEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["nomEtapeEvenement"],
                            "nomFormation" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"],
                            "nomAnnee" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomAnnee"],
                            "nomStatut" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomStatut"],
                            "nomApprenant" => $prospect["nomApprenant"],
                            "prenomApprenant" => $prospect["prenomApprenant"]
                        );
                    }
                }
            }

                     
        }

        if (isset($prospects_tab)) {
            return $prospects_tab;
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
        
        foreach ($api_data_frequentes_precedent as $frequente) {
                
            $frequente_tab_precedent[$frequente["codeApprenant"]] = $frequente["codeApprenant"];
        
        } 

        foreach ($api_data_apprenants_precedent as $apprenant) {  

            if (!empty($frequente_tab_precedent[$apprenant["codeApprenant"]])) {
                for ($i=0; $i < count($apprenant["inscriptions"]) ; $i++) { 
                    if ($apprenant["inscriptions"][$i]["isInscriptionEnCours"] == 1) {
                        $apprenants_tab_precedent[$apprenant["codeApprenant"]] = $apprenant["inscriptions"][$i]["formation"]["nomFormation"];
                    }
                }
                
            }
                        
        }
                
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

        //Tableau entreprise
        $entreprises_tab = Cache::get('entreprises_tab');
        if (empty($entreprises_tab)) {

            $api_data_entreprises = $this->ApiEntreprises();

            // tableau avec code entreprise en key et le nom en valeur 
            foreach ($api_data_entreprises as $entreprise) {
                $entreprises_tab[$entreprise["codeEntreprise"]] = $entreprise["nomEntreprise"];
            }

            Cache::put('entreprises_tab', $entreprises_tab, 32000);
        }

 

        // tableau apprenant complete + creation tableau formation

        foreach ($api_data_apprenants as $apprenant) {
            
            //si l'apprenant et dans la table de frequentation
            if (!empty($frequente_tab[$apprenant["codeApprenant"]])) {     
                
                //tout apprenant et nouveau sauf s'il est dans la table apprenant et que son nom de formaton est le meme
                $nouveau = 1;
                if (!empty($apprenants_tab_precedent[$apprenant["codeApprenant"]])) {

                    $nomfomation = $apprenant["inscriptions"][0]["formation"]["nomFormation"];
                    $nomfomation_precedent = $apprenants_tab_precedent[$apprenant["codeApprenant"]];
                    if ($nomfomation == $nomfomation_precedent) {
                        $nouveau = 0;
                    }           
                }

                $apprenants_tab[$apprenant["codeApprenant"]] = array(
                    "nomApprenant" => $apprenant["nomApprenant"],
                    "prenomApprenant" => $apprenant["prenomApprenant"],
                    "dateCreation" => $apprenant["dateCreation"],
                    "nouveau" => $nouveau,
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

        if (isset($apprenants_tab)) {
            return  $apprenants_tab;
        }else {
            return  null;
        } 
       
    }


    protected function AffichageFormation(Request $request)
    {

        $formation = $request->input('formation');
        $annee = $request->input('annee');

        $tableau_complet = $request->session()->get('tableau_complet_flash');
        //dd($tableau_complet);
        $date = $request->input('date');
        echo("{nom formation : $formation}, annee :{$annee}");

        //$tableau_complet_cache = $request->session()->get('tableau_complet');
        //$tableau_complet_cache = Cache::get('tableau_complet_cache');
        
        if (empty($tableau_complet)) {
            echo("tableau_complet_cache vide");
            exit;
            //$this->index();
        }

        foreach ($tableau_complet as $individu) {
            if ($individu['nomFormation'] == $formation && $individu["nomAnnee"] == $annee) {
                $tableau_complet_formation[]= $individu;
            }
        }

        if (empty($tableau_complet_formation)) {
            echo("tableau_complet_formation vide");
            exit;
            //$this->index();
            
        }


        //dd($tableau_complet_formation);

        return view('affichageformation')
                ->with(compact('formation'))
                ->with(compact('annee'))
                ->with(compact('date'))
                ->with(compact('tableau_complet_formation'));

    }

    public function PrevisDataBase(Request $request)
    {
        
        $periode = $request->get('periode');
        $date = $request->get('date');
        //$date = '2022-07-08';
       // echo('indexSaveDatabase');
       dd($request->input());
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
                  'nomAnnee' => '2ème année',
                  'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
                  'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',

                ),
                888888 => 
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
                //dump($tab1);
        $tab = array_merge($tab1, $tab2);
        //dd($tab);


       


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
        foreach ($prospects_tab as $code => $prospect) {
            if (!empty($contrats_tab[$code])) {
                  $count++;
            }
        } 

        echo("nombre de prsopect avec contrat");
        dump($count);
    
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
/*         $code = ;
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
                echo('prospect');          
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
            
            //si l'apprenant et dans la table de frequentation
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
}
