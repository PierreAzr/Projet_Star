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

    public function index(Request $request)
    {   

        //vider tous le cache
        //Cache::flush();

        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        
        // Recuperation de la table periode et trouver la periode qui correspond a la date voulu (date du jour par defaut) ainsi que la periode n-1
         
         //On recupere la date s'il y en a une
         $date = $request->get('date');
         echo("voici la date {$date}");
        
        //Requete api
        $api_data_periodes = $this->ApiPeriodes();
        //dd($api_data_periodes);


        if(empty($date)){
            $date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );
            $date_annee_precedente = date_create_from_format('d/m/Y', date("d/m/Y") )->modify('-1 year');
            //$date_annee_precedente = date_create_from_format('d/m/Y', date("d/m/Y", strtotime("-1 year")) );

        }else {
            $date_du_jour = date_create_from_format('Y-m-d', $date ) ;
            $date_annee_precedente = date_create_from_format('Y-m-d', $date )->modify('-1 year');
        }

        foreach ($api_data_periodes as $periode) {

            $dateDeb = date_create_from_format('d/m/Y', $periode["dateDeb"] );
            $dateFin = date_create_from_format('d/m/Y', $periode["dateFin"] );

            if ($dateDeb <= $date_du_jour && $date_du_jour < $dateFin) {
                $code_periode_actuel = $periode["codePeriode"];
            }

            //preiode precedentes
            if ($dateDeb <= $date_annee_precedente && $date_annee_precedente < $dateFin) {
                $code_periode_precedente = $periode["codePeriode"];
            }
            
        }

        
        // Fin calcul Periode


        //dd($code_periode_actuel);


        $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);
        $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente);
        $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel); 
        $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente); 

        $api_data_prospects = $this->ApiProspects($code_periode_actuel);
        $api_data_contrats = $this->ApiContrats($code_periode_actuel);
        $api_data_entreprises = $this->ApiEntreprises();
        $api_data_groupes = $this->ApiGroupes($code_periode_actuel);//cache
        
        
    
  
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


        // tableau avec code entreprise en key et le nom en valeur 
        foreach ($api_data_entreprises as $entreprise) {
            $entreprises_tab[$entreprise["codeEntreprise"]] = $entreprise["nomEntreprise"];
        }

        //tableau groupes
        foreach ($api_data_groupes as $groupe) {
            $groupes_tab[$groupe["codeGroupe"]] = array(
                "nomGroupe" =>$groupe["nomGroupe"],
                "capaciteMax" => $groupe["capaciteMax"]
            );
        }
 
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

        $count = 0;
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

     
        // tableau apprenant complete + creation tableau formation
        $formation_tab = [];
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
                //creation tableau formation
                $code_groupe = $apprenant["informationsCourantes"]["codeGroupe"];     
                if (!empty($groupes_tab[$code_groupe])) {
                    array_push($formation_tab, array(
                        "nomSecteurActivite" => $apprenant["inscriptions"][0]["formation"]["nomSecteurActivite"], 
                        "nomGroupe" => $groupes_tab[$code_groupe]["nomGroupe"],
                        "nomFormation" => $apprenant["inscriptions"][0]["formation"]["nomFormation"],
                        "nomAnnee" => $apprenant["inscriptions"][0]["situation"]["nomAnnee"],
                        "capaciteMax" =>  $groupes_tab[$code_groupe]["capaciteMax"]
                        //"capaciteMax" =>  0
                    )
                    );

                }

            }
        }


        //recupere un tableau et suprime tous les doublons
        $formation_secteur_tab = array_map("unserialize", array_unique(array_map("serialize", $formation_tab)));
        //re-index
        $formation_secteur_tab = array_values($formation_secteur_tab);
        //tri du trableau
        $columns_1 = array_column($formation_secteur_tab, 'nomSecteurActivite');
        $columns_2 = array_column($formation_secteur_tab, 'nomFormation');
        $columns_3 = array_column($formation_secteur_tab, 'nomAnnee');
        $formation_secteur_tab_tri =  array_multisort($columns_1, SORT_ASC, $columns_2, SORT_ASC, $columns_3, SORT_ASC, $formation_secteur_tab);
       
        

        //tableau Prospects complete
        foreach ($api_data_prospects as $prospect) {

            $codeEtape = $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {
                //si l'apprenant n'est pas en cours de formation alors il est prospect
                //if (empty($frequente_tab[$prospect["codeApprenant"]])) {             
                if ($prospect["estProspect"] == 1) {
                    $prospects_tab[$prospect["codeApprenant"]] = array(
                        "codeEtapeEvenement" => $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"],
                        "nomEtapeEvenement" => $prospect["evenementsRacines"][0]["dernierEvenement"]["nomEtapeEvenement"],
                        "nomFormation" => $prospect["formationsSouhaitees"][0]["nomFormation"],
                        "nomAnnee" => $prospect["formationsSouhaitees"][0]["nomAnnee"],
                        "nomStatut" => $prospect["formationsSouhaitees"][0]["nomStatut"],
                        "dateCreation" => $prospect["dateCreation"],
                        "nomApprenant" => $prospect["nomApprenant"],
                        "prenomApprenant" => $prospect["prenomApprenant"]
                    );

                }
            }
                     
        }

        $tableau_complet = array_merge($prospects_tab, $apprenants_tab);
        //$tableau_complet_cache = Cache::get('tableau_complet_cache');
        if (empty($tableau_complet_cache)) {
            Cache::put('tableau_complet_cache', $tableau_complet, 360);
        }

        

        $final_tab = [];
        $precontrat_total=0;
        $reception_contrat_total=0;
        $contrat_recu_total=0;
        $nouveau_total=0;
        $ancient_total=0;
        $count_total=0;

        foreach ($formation_secteur_tab as $formation) {

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
                "nomSecteurActivite" => $formation["nomSecteurActivite"], 
                "nomFormation" => $formation["nomFormation"],
                "nomGroupe" => $formation["nomGroupe"],
                "nomAnnee" => $formation["nomAnnee"],
                "precontrat" => $precontrat,
                "receptioncontrat" => $reception_contrat,
                "contratrecu" => $contrat_recu,
                "nouveau" => $nouveau,
                "ancient" => $ancient,
                "total" => $count,
                "capaciteMax" => $formation["capaciteMax"],
                "nbPlacePossible" => $formation["capaciteMax"] - $count,

                

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
        
        //$d_microtime = microtime(true);
        //$d_memory = memory_get_usage(true);

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo("temps execution : {$time}, Memoire utlisé : {$memory}"); 
        //dd($tableau_complet);
        //dump($apprenants_tab);
        //dump($formation_secteur_tab);
        //exit;
        return view('relationentreprise')
                ->with(compact('final_tab'))
                ->with(compact('total_tab'))
                ->with(compact('date'));

    }


    public function AffichageFormation($formation, $annee)
    {
        echo("{nom formation : $formation}, annee :{$annee}");

        $tableau_complet_cache = Cache::get('tableau_complet_cache');
        
        if (empty($tableau_complet_cache)) {
            echo("tableau_complet_cache vide");
            exit;
            //$this->index();
        }

        foreach ($tableau_complet_cache as $individu) {
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
                ->with(compact('tableau_complet_formation'));

    }


}
