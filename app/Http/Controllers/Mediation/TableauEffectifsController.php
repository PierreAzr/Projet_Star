<?php

namespace App\Http\Controllers\Mediation;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Traits\ApiRequestTrait;


//mise en cache
use Illuminate\Support\Facades\Cache;

use App\Models\Formations;
use App\Models\Previs;

class TableauEffectifsController extends Controller
{
    
    use ApiRequestTrait;

    public function Effectifs(Request $request)
    {   
        //cache::flush();
        $test = Previs::get();
        dump($test);
        $formations = Formations::get(); 
        dd( $formations);
        exit;
        
        //On recupere la date s'il y en a une
        $date = $request->get('date');

        if(empty($date)){

            // si la date et vide on prend la date du jour
            $date_vide = true;
            $date = date("Y-m-d");
            $date_choisi = date_create();
            $date_annee_precedente = date_create()->modify('-1 year');

        }else {

            $date_choisi = date_create_from_format('Y-m-d', $date ) ;
            $date_annee_precedente = date_create_from_format('Y-m-d', $date )->modify('-1 year');
        }

        //Requete api PERIODES
        $api_data_periodes = $this->ApiPeriodes();
        // on cherche la periode actuel et precedente
        foreach ($api_data_periodes as $periode) {

            $date_debut_periode = date_create_from_format('d/m/Y', $periode["dateDeb"] );
            $date_fin_periode = date_create_from_format('d/m/Y', $periode["dateFin"] );

            // on determine la periode actuel 
            if ($date_debut_periode <= $date_choisi && $date_choisi <= $date_fin_periode) {
                $code_periode_actuel = $periode["codePeriode"];
                $periode_actuel = $periode["nomPeriode"];
            }

            // on determine periode precedente
            if ($date_debut_periode <= $date_annee_precedente && $date_annee_precedente <= $date_fin_periode) {
                $code_periode_precedente = $periode["codePeriode"];
            }
            
        }

        // si la periode n'existe pas on renvoi le tableau avec la date d'aujourdui
        if(!isset($code_periode_actuel)){
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond a une periode qui n'existe pas encore")
            ->with('flash_type', 'alert-danger');
        }


        //Si la date_choisi est la date du jour on recupere le cache
        //$date_vide = null;
        if(!empty($date_vide)){

            $tableau_complet = Cache::get('tableau_complet_date_vide');
            $final_tab = Cache::get('final_tab_date_vide');
            $total_tab = Cache::get('total_tab_date_vide');     
            $prospects_plusieurs_formation = Cache::get('prospects_plusieurs_formation_date_vide');
            $erreur = Cache::get('erreur_date_vide');
            $commun_tab = Cache::get('commun_tab_date_vide');
            $previs = Cache::get('previ_date_vide');

            if (isset($tableau_complet) && isset($final_tab) && isset($total_tab) && isset($prospects_plusieurs_formation) && isset($erreur) && isset($commun_tab)) {       
                echo("***********datevide***********");
                return view('mediation.tableaueffectifs')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('tableau_complet'))
                ->with(compact('date'))
                ->with(compact('periode_actuel'))
                ->with(compact('previs'))
                ->with(compact('prospects_plusieurs_formation'))
                ->with(compact('erreur'))
                ->with(compact('commun_tab'));
            }

        }

        //on recupere la table des apprenants 
        $apprenants_tab = $this->ApprenantsTab($date_choisi,$code_periode_actuel,$code_periode_precedente);
    

        //Recupere la table des prospects voulu et la table formation multiple
        $prospects_tab_temp = $this->ProspectsTab();

        //Si la table prospects existe on recupere les tables sinon null 
        if (isset($prospects_tab_temp)) {
            $prospects_tab = $prospects_tab_temp['prospects_tab'];
            $prospects_plusieurs_formation = $prospects_tab_temp['prospects_plusieurs_formation'];
        }else {
            $prospects_tab = null;
            $prospects_plusieurs_formation = null;
        }
       

        //on verifie les prospects et les apprenant commun
        $commun_tab = null;
         foreach ($prospects_tab as $codeApprenant => $prospect) {
            if(!empty($apprenants_tab[$codeApprenant])){

                $commun_tab[$codeApprenant] = array(
                    "nomApprenant" => $prospect["nomApprenant"],
                    "prenomApprenant" => $prospect["prenomApprenant"],
                    "nomFormation" => $apprenants_tab[$codeApprenant]["nomFormation"],
                    "nomAnnee" => $apprenants_tab[$codeApprenant]["nomAnnee"],
                    "nomFormationp" => $prospect["nomFormation"],
                    "nomAnneep" => $prospect["nomAnnee"],
                    "nomEtapeEvenement" => $prospect["nomEtapeEvenement"],
                );

            }
        } 

        // Construction du tableau complet contenant les apprenants et les prospects
        if (isset($prospects_tab) && isset($apprenants_tab)) {

            // ordre de l'addition est importante, le tableau apprenants ecrase le tableau prospects 
            $tableau_complet =  $apprenants_tab + $prospects_tab;

        }elseif (isset($apprenants_tab)) {

            $tableau_complet = $apprenants_tab;

        }elseif (isset($prospects_tab)){

            $tableau_complet = $prospects_tab;

        }else{
            return redirect()->route('relation_entreprise_index')->with('flash_message', "La date correspond à une periode qui existe mais il n'y a encore ni prospects ni apprenant")
            ->with('flash_type', 'alert-danger');  
        }
       

        //## Requete de la base de donnée
        $formations = Formations::get(); 

        $previs = Previ::where('previs.periode', $periode_actuel)->get();

        // On compte par formation le nombre individu qui correspond aux colonne voulu
        //Construction du tableau final et du tableau total pour la vue
        $liste_tableau = $this->ConstructionTableauFinal($tableau_complet, $formations);
            $final_tab = $liste_tableau['final_tab'];
            $total_tab = $liste_tableau['total_tab'];
        

        //##erreur
        $erreur = $this->Erreur($tableau_complet, $formations);
        //$erreur=null;

        // mise en cache dans le cas ou la date est vide et donc on prend la date du jour
        if(isset($date_vide)){
            echo('sesesese  set date vide seseseeseees');
            Cache::put('final_tab_date_vide', $final_tab, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('total_tab_date_vide', $total_tab, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('tableau_complet_date_vide', $tableau_complet, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('prospects_plusieurs_formation_date_vide', $prospects_plusieurs_formation, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('erreur_date_vide', $erreur, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('commun_tab_date_vide', $commun_tab, env('TEMP_CACHE_CONTROLLER'));
            Cache::put('previ_date_vide', $previs, env('TEMP_CACHE_CONTROLLER'));
            

        }
        
        return view('mediation.tableaueffectifs')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('tableau_complet'))
                ->with(compact('date'))
                ->with(compact('periode_actuel'))
                ->with(compact('previs'))
                ->with(compact('prospects_plusieurs_formation'))
                ->with(compact('erreur'))
                ->with(compact('commun_tab'));

    }

    public function ApprenantsTab($date_choisi,$code_periode_actuel,$code_periode_precedente)
    {

        //##requete api
        $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);

        //creation de la table frequentation
        $frequente_tab=[];
        foreach ($api_data_frequentes as $frequente) {

            // on verifie que la date
            $date_fin = date_create_from_format('d/m/Y', $frequente["dateFin"]);
            $date_deb = date_create_from_format('d/m/Y', $frequente["dateDeb"]);
            if( (empty($frequente["dateFin"]) || $date_choisi < $date_fin) && ($date_choisi > $date_deb)) {
                            
                $frequente_tab[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );

            }
        }
                
        //## requete api
        $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente);        
        
        // Construction de la table de fraquantation de l'année precedente
        foreach ($api_data_frequentes_precedent as $frequente) {
                
            $frequente_tab_precedent[$frequente["codeApprenant"]] = $frequente["codeApprenant"];
        
        } 

       //## requete api
        $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente); 

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
          
        //## requete api
        $api_data_contrats = $this->ApiContrats($code_periode_actuel);     

        // Creation de la table contrat depuis la requete api
        foreach ($api_data_contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            //$date_choisi = date_create_from_format('d/m/Y', date("d/m/Y") );

            // on garde les contrats qui non pas de date de resiliation et qui sont relier a une entreprise
            // les contrats qui nom pas d'entreprise corresponde a un contrat intermedaire suite a une rupture en attandant que l'apprenant trouve une nouvel entreprise
            if (empty($contrat["dateResiliation"]) && !empty($contrat["codeEntreprise"])) {
      
                if ($date_fin > $date_choisi) {

                    $contrats_tab[$contrat["codeApprenant"]] = array(
                        "codeEntreprise" =>$contrat["codeEntreprise"],
                        "codeContrat" => $contrat["codeContrat"],
                        "dateDebContrat" => $contrat["dateDebContrat"]
                    );
                
                }
            }

        }

        // Creation de la table entreprise
        //Tableau entreprise trier mis en cache car indepandant de la date et de la periode
        //deja mis en cache en brute dans le traits
        $entreprises_tab = Cache::get('entreprises_tab');
        if (empty($entreprises_tab)) {

            //## requete api
            $api_data_entreprises = $this->ApiEntreprises();

            // Tableau avec code entreprise en clef et le nom en valeur 
            foreach ($api_data_entreprises as $entreprise) {
                $entreprises_tab[$entreprise["codeEntreprise"]] = $entreprise["nomEntreprise"];
            }

            Cache::put('entreprises_tab', $entreprises_tab, env('TEMP_CACHE_CONTROLLER'));
        }

        //## requete api
        $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);

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
                            "CodeApprenant" =>$apprenant["codeApprenant"],
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

    protected function ProspectsTab()
    {

         // Date du jour et on prend un an avant(choix arbitraire) (imposible de rechercher prospect dans le futur la date correspond a la date de l'evenement)
         $date_debut_prospect = date_create()->modify('-1 year')->format('d-m-Y');
         $date_fin_prospect = date('d-m-Y');
 
         // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
         // codeTypeEvt trouver avec la table typeEvenement https://citeformations.ymag.cloud/index.php/r/v1/types-evenement

         
        // on fais Trois requetes a la fonction ProspectEvenement (fait la requete api est renvoi un tableau contenant les codeApprenant)
        // on recupere le code apprenant des prospects avec l'evenement voulu et on met en cache
        $api_prospects_tab_recu = Cache::get('api_prospects_tab_recu');
        if (empty($api_prospects_tab_recu)) {
            //## requete api
            $api_prospects_tab_recu = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=8, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('api_prospects_tab_recu', $api_prospects_tab_recu, env('TEMP_CACHE_CONTROLLER'));
        }
        
        $api_prospects_tab_reception = Cache::get('api_prospects_tab_reception');
        if (empty($api_prospects_tab_reception)) {
            //## requete api
            $api_prospects_tab_reception = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=151, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('api_prospects_tab_reception', $api_prospects_tab_reception, env('TEMP_CACHE_CONTROLLER'));
        }

        $api_prospects_tab_envoi = Cache::get('api_prospects_tab_envoi');
        if (empty($api_prospects_tab_envoi)) {
            //## requete api
            $api_prospects_tab_envoi = $this->ApiProspectsEvenement($codeTypeEvt=4, $codeEvenement=149, $date_debut_prospect, $date_fin_prospect, $evtClotures=0);
            Cache::put('api_prospects_tab_envoi', $api_prospects_tab_envoi, env('TEMP_CACHE_CONTROLLER'));
        }
        
        $prospect_evenement = array_merge($api_prospects_tab_envoi , $api_prospects_tab_reception, $api_prospects_tab_recu );

        //Creation de la table prospects voulu
        $prospects_plusieurs_formation = [];

        foreach ($prospect_evenement as  $prospect) {

            //un prospect peu avoir plusieurs evenement racine, on prend le dernier qui correspond au dernier en date
            $nombre_evenement_racine = count($prospect["evenementsRacines"]); 
            $dernier_evenement_racine = $prospect["evenementsRacines"][$nombre_evenement_racine - 1]; 

            // si un evenement choisi c'est passe sur la periode mais n'est pas le dernier
            // on verifie que le dernier evenement et un des bon codeEtape
            //exemple cas prospects l'annee derniere non cloturé a un bon codeetape et de nouveau prospect
            $codeEtape = $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {
        
                //Construction du tableau prsopects
                //Attention certain prospect on plusieur formation souhaite on prend la premiere 
                $prospects_tab[$prospect["codeApprenant"]] = array(
                    "CodeApprenant" =>$prospect["codeApprenant"],
                    "nomApprenant" => $prospect["nomApprenant"],
                    "prenomApprenant" => $prospect["prenomApprenant"],
                    "nomFormation" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomFormation"],
                    "nomAnnee" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomAnnee"],
                    "nomStatut" => $dernier_evenement_racine["formationsSouhaitees"][0]["nomStatut"],
                    "codeEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["codeEtapeEvenement"],
                    "nomEtapeEvenement" => $dernier_evenement_racine["dernierEvenement"]["nomEtapeEvenement"],

                );

                //construction tableau prospect plusieurs formation
                $nombre_formation = count($dernier_evenement_racine["formationsSouhaitees"]);
                if ( $nombre_formation > 1) {
                    
                    $prospects_plusieurs_formation[$prospect["codeApprenant"]] = array(
                        "nomApprenant" => $prospect["nomApprenant"],
                        "prenomApprenant" => $prospect["prenomApprenant"],
                        );  

                    for ($i=0; $i < $nombre_formation ; $i++) {

                        $prospects_plusieurs_formation[$prospect["codeApprenant"]] += array(
                            "nomFormation$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomFormation"],
                            "nomAnnee$i" => $dernier_evenement_racine["formationsSouhaitees"][$i]["nomAnnee"],
                        );
                    }  
                    
                }        

            }
    
        }

        if (isset($prospects_tab)) {
            return array("prospects_tab" => $prospects_tab, "prospects_plusieurs_formation" => $prospects_plusieurs_formation );
        }else {
            return  null;
        }
        
    }

    public function ConstructionTableauFinal($tableau_complet, $formations)
    {
            
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

    public function PrevisDataBase(Request $request)
    {
        
        $periode = $request->get('periode');
        $date = $request->get('date');

        foreach ($request->input() as $key => $value) {
            if (is_int($key)) {      
                if(is_numeric($value)) {      
                    Previ::updateOrCreate(
                        ['idFormation' => $key, 'periode' => $periode],
                        ['previ' => $value ]
                    );
                }else {
                    return redirect()->route('mediation_tableau_effectifs', ['date' => $date])->with('flash_message', 'Erreur les champs doivent être des nombres')
                    ->with('flash_type', 'alert-danger');
                }
            }
        }

       return redirect()->route('mediation_tableau_effectifs', ['date' => $date])->with('flash_message', 'Previsionel enregitrer')
                                                    ->with('flash_type', 'alert-success');

    }

    public function Erreur($tableau_complet,$formations)
    {
        
        $liste_annee_null = [];
        $liste_annee_mauvaise = [];
        $liste_formation_existe_pas = [];
        foreach ($tableau_complet as $individu) {

            $mauvaise_annee = True;
            $formation_existe_pas = True;
            foreach ($formations as $formation) {

                if($individu['nomFormation'] == $formation["nomFormation"] ){
                    // la formation existe bien dans la basse de donnée
                    $formation_existe_pas = False;
           
                    if($individu["nomAnnee"] == $formation["nomAnnee"]){
                        // l'annee et la formation existe bien
                        $mauvaise_annee = False;
                    }
                }

            }

            // liste des prospects/apprenants avec un mauvais nom d'année
            if ($mauvaise_annee) {
                if(empty($individu["nomAnnee"])){
                    array_push($liste_annee_null, $individu["CodeApprenant"]);                   
                }else{
                    array_push($liste_annee_mauvaise, $individu["CodeApprenant"]);            
                }
            }

            //liste des prospects/apprenants avec une formation qui n'est pas dans la basse
            if ($formation_existe_pas) {            
                if ($individu["nomFormation"] !='ERASMUS POST-APPRENTISSAGE') {
                    array_push($liste_formation_existe_pas, $individu["CodeApprenant"]);  
                }       
            }

        }
        
        if (!empty($liste_annee_mauvaise) && !empty($liste_annee_null)) {
            return array('liste_annee_mauvaise' => $liste_annee_mauvaise,
            'liste_annee_null' => $liste_annee_null,
            'liste_formation_existe_pas' => $liste_formation_existe_pas,
            //'liste_fomation_erasmus' => $liste_fomation_erasmus,
            );
        }else {
            return  null;
        } 



    }


}//fin class
