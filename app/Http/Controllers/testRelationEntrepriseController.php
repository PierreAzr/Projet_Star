<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\ApiRequestTrait;

//pour utilise requete api
use Illuminate\Support\Facades\Http;
//mise en cache
use Illuminate\Support\Facades\Cache;

class testRelationEntrepriseController extends Controller
{
    
    use ApiRequestTrait;

    public function index(Request $request)
    {   

        //vider tous le cache
        //Cache::flush();


        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        $api_data_contrats = $this->ApiContrats(11);

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo("temps execution : {$time}, Memoire utlisé : {$memory}"); 
        dump($api_data_contrats);

        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        $api_data_contrats2 = $this->ApiContrats();

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo("temps execution : {$time}, Memoire utlisé : {$memory}"); 
        dump($api_data_contrats2);
        exit;


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

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/30-08-2020/28-08-2022/0";
        $api_data_pr_recu = $this->ApiCall($url);  
        echo("periode 2021-2022 contrat recu");
        dump($api_data_pr_recu);
        foreach ($api_data_pr_recu as $prospect) {
                $prospects_tab_recu[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
        
        }
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/151/30-08-2021/28-08-2022/0";
        $api_data_pr_reception = $this->ApiCall($url);  
        echo("periode 2021-2022 reception pre contrat");
        //dump($api_data_pr_reception);
        foreach ($api_data_pr_reception as $prospect) {
            $prospects_tab_reception[$prospect["codeApprenant"]] = array(
                "codeApprenant" => $prospect["codeApprenant"]
            );
        }
    
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/149/30-08-2021/28-08-2022/0";
        $api_data_pr_envoi = $this->ApiCall($url);  
        echo("periode 2021-2022 envoi pre contrat");
       // dump($api_data_pr_envoi);   
        foreach ($api_data_pr_envoi as $prospect) {
            $prospects_tab_envoi[$prospect["codeApprenant"]] = array(
                "codeApprenant" => $prospect["codeApprenant"]
            );
        }

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=15";
        $api_data_prospects_15 = $this->ApiCall($url);
        echo("prospect periode 15");
        //dump($api_data_prospects_15);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events";
        $api_data_prospects_11 = $this->ApiCall($url);
        echo("prospect periode 11");
        //dump($api_data_prospects_11);

        foreach ($api_data_prospects_15 as $prospect) {

            if (!empty($prospects_tab_recu[$prospect["codeApprenant"]])) {     
                $prospects_tab_recu_15[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }

            if (!empty($prospects_tab_reception[$prospect["codeApprenant"]])) {     
                $prospects_tab_reception_15[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }

            if (!empty($prospects_tab_envoi[$prospect["codeApprenant"]])) {     
                $prospects_tab_envoi_15[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }
        }
        echo("prospect periode 15 recu");
        dump($prospects_tab_recu_15);
        echo("prospect periode 15 reception");
        dump($prospects_tab_reception_15);
        echo("prospect periode 15 envoi");
        dump($prospects_tab_envoi_15);

        foreach ($api_data_prospects_11 as $prospect) {

            if (!empty($prospects_tab_recu[$prospect["codeApprenant"]])) {     
                $prospects_tab_recu_11[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }

            if (!empty($prospects_tab_reception[$prospect["codeApprenant"]])) {     
                $prospects_tab_reception_11[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }

            if (!empty($prospects_tab_envoi[$prospect["codeApprenant"]])) {     
                $prospects_tab_envoi_11[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );
            }
        }
        echo("prospect periode 11 recu");
        dump($prospects_tab_recu_11);
        echo("prospect periode 11 reception");
        dump($prospects_tab_reception_11);
        echo("prospect periode 11 envoi");
        dump($prospects_tab_envoi_11);





    exit;
    // test prospect avec parametre cloture
    //##########################################################################
      /*   //9936 "idFormationSouhait1" => "398436" "idFormationSouhait2" => "203348"
        //table candidat r/v1/preinscription/candidat/@codeCandidat
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/preinscription/candidat/9936";
        $api_data_pr = $this->ApiCall($url);  
        dump($api_data_pr);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/preinscription/candidat/9936";
        $api_data_pr = $this->ApiCall($url);  
        dump($api_data_pr);


        exit;
        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/types-evenement";
        $api_data_pr = $this->ApiCall($url);  

        //dump($api_data_pr);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects/4/151/22-06-2022/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2022 reception pre contrat ");
        dump($api_data_pr);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/22-01-2021/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2022 envoie pre contrat");
        dump($api_data_pr);
      
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/22-01-2021/27-08-2023/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2023 envoie pre contrat");
        dump($api_data_pr);

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/30-08-2021/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2021-2022 contrat recu");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/151/30-08-2021/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2021-2022 reception pre contrat");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/149/30-08-2021/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2021-2022 envoie pre contrat");
        dump($api_data_pr);

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/22-06-2022/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2022 contrat recu");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/151/22-06-2022/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2022 reception pre contrat");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/149/22-06-2022/28-08-2022/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode date-2022 envoie pre contrat");
        dump($api_data_pr);
      
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/8/29-08-2022/27-08-2023/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2022-2023 contrat recu");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/151/29-08-2022/27-08-2023/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2022-2023 reception pre contrat");
        dump($api_data_pr);
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/4/149/29-08-2022/27-08-2023/0";
        $api_data_pr = $this->ApiCall($url);  
        echo("periode 2022-2023 envoie pre contrat");
        dump($api_data_pr);

        exit;
 */
        
        
        //test performance requetes pas en cache
        //#############################################################################
/* 
        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        $token_api = env('TOKEN_YPAREO');
        $header_token = "X-Auth-Token";
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/apprenants?codesPeriode=".$code_periode_actuel;
        // ATTENTION uniquement en LOCAL withoutVerifying permet de pas controler les certificat
        $response = Http::withoutVerifying()
        ->withHeaders([$header_token => $token_api])
        ->get($url);

        $data = json_decode($response, true);  
        


        $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);
        $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente);
        $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);
        $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente);


        //$url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/apprenants?codesPeriode=11";
        //$api_data_apprenants = $this->ApiCallCurl($url);

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo('contrat avec liste ->');
        echo("temps execution : {$time}, Memoire utlisé : {$memory}");
        exit; */


        //requetes pas de cache
 /*     $api_data_apprenants = $this->ApiApprenants($code_periode_actuel);
        $api_data_apprenants_precedent = $this->ApiApprenants($code_periode_precedente);
        $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel); 
        $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente); */
        
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
        
        $api_data_frequentes = Cache::get('api_data_frequentes');
        if (empty($api_data_frequentes)) {
            $api_data_frequentes = $this->ApiFrequentes($code_periode_actuel);
            Cache::put('api_data_frequentes', $api_data_frequentes, 32000);
        } 

       
        $api_data_frequentes_precedent = Cache::get('api_data_frequentes_precedent');
        if (empty($api_data_frequentes_precedent)) {
            $api_data_frequentes_precedent = $this->ApiFrequentes($code_periode_precedente);
            Cache::put('api_data_frequentes_precedent', $api_data_frequentes_precedent, 32000);
        }  



        //autre requete
        $api_data_prospects = $this->ApiProspects($code_periode_actuel);
        $api_data_contrats = $this->ApiContrats($code_periode_actuel);
        $api_data_entreprises = $this->ApiEntreprises();
        $api_data_groupes = $this->ApiGroupes($code_periode_actuel);//cache
        
        //test cration tableau formation a partir de la table formation et groupe //pas possible
        //########################################
/*         $api_data_groupes = $this->ApiGroupes($code_periode_actuel);//cache
        $api_data_formations = $this->ApiFormations();
        dd($api_data_groupes);

        foreach ($api_data_formations as $formation) {
            
            if ($formation["plusUtilise"] != 1) {
                $formations_tab[$formation["codeFormation"]] = array(
                    "nomSecteurActivite" => $formation["nomSecteurActivite"],
                    "nomFormation" => $formation["nomFormation"],
                );
            }

        }

        $formation_tab_complete = [];
        foreach ($api_data_groupes as $groupe) {

            if (!empty($formation_tab[$groupe["codeFormation"]])) {
                array_push($formation_tab_complete, array(
                    "capaciteMax" => $groupe["capaciteMax"],
                    "numeroAnnee" => $groupe["numeroAnnee"],
                    "nomSecteurActivite" => $formation_tab[$groupe["codeGroupe"]]["nomSecteurActivite"],
                    "nomFormation" => $formation_tab[$groupe["codeGroupe"]]["nomFormation"]
                    )
                );
            }  */


        // pour vour un apprenant sur les diferente table
        //####################################################"

/*         $codeApprenant = 645347;// plusieur frequentation et inscription
        //$codeApprenant =839204; // pas dans la table apprenants
        foreach ($api_data_apprenants as $apprenant) {
            if ($apprenant["codeApprenant"] ==  $codeApprenant ){
                echo("Apprenant");
                dump($apprenant);

            } 
        }
        foreach ($api_data_frequentes as $frequente) {
            if ($frequente["codeApprenant"] ==  $codeApprenant ){
                echo("Frequente");
                dump($frequente);

            } 
        }
        foreach ($api_data_prospects as $prospect) {
            if ($prospect["codeApprenant"] ==  $codeApprenant ){
                echo("prospect");
                dump($prospect);

            }
        }

        $app = [];
        foreach ($api_data_apprenants as $apprenant) {

            Array_push($app, $apprenant["codeApprenant"] );

            
        }
        
        $ap = array_count_values($app);
        dd($app);
        $app2 = [];
        foreach ($ap as $apprenant) {
            if ($apprenant > 1) {
                Array_push($app2, $apprenant );
            }
            

            
        }
        dump(array_count_values($app2));
        exit;  */

        /* $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events";
        $api_data_prospects = $this->ApiCall($url);
        dump($api_data_prospects);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=11";
        $api_data_prospects_code = $this->ApiCall($url);
        dump($api_data_prospects_code);
        exit; */



       // dump($dateDeb);
        //dump($date_du_jour);
        //dump($date_annee_precedente);
        //dump($api_data_periodes);
        //exit;




        //code tri colonne et index des requetes
        //#############################################
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

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 
/*         $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=15";
        $api_data_prospects = $this->ApiCall($url);

        $count_prospect_ok = 0;
        $count_prospect_non_ok = 0;
        foreach ($api_data_prospects as $prospect) {

            $codeEtape = $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) { 
                
                    if ($prospect["estProspect"] == 0) {
                        $count_prospect_non_ok++;
                    }else {
                        $count_prospect_ok++;
                    } 
            }
                     
        }
        echo("periode 15");
        echo("Nombre de prospect dans la table avec les bon codes etape et estprospect = 0 :");
        dump($count_prospect_non_ok);
        echo("Nombre de prospect dans la table avec les bon codes etape et estprospect = 1 :");
        dump($count_prospect_ok);


        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=10";
        $api_data_prospects = $this->ApiCall($url);

        $count_prospect_ok = 0;
        $count_prospect_non_ok = 0;
        foreach ($api_data_prospects as $prospect) {

            $codeEtape = $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) { 
                
                    if ($prospect["estProspect"] == 0) {
                        $count_prospect_non_ok++;
                    }else {
                        $count_prospect_ok++;
                    } 
            }
                     
        }
        echo("periode 10");
        echo("Nombre de prospect dans la table avec les bon codes etape et estprospect = 0 :");
        dump($count_prospect_non_ok);
        echo("Nombre de prospect dans la table avec les bon codes etape et estprospect = 1 :");
        dump($count_prospect_ok);



        $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/frequentes?codesPeriode=15";
        $api_data_frequentes = $this->ApiCall($url);

        $count = 0;
        $count2 = 0;
        foreach ($api_data_frequentes as $frequente) {

            $count++;

            $date_fin = date_create_from_format('d/m/Y', $frequente["dateDeb"]);
            if( $date_du_jour > $date_fin) {
                

                $count2++;
            }
        }
        echo("periode 15");
        echo("Nombre personnes table frequentes:");
        dump($count);
        echo("Nombre personnes table frequentes date courant de la periode d'avant:");
        dump($count2);

        exit;
 */

      /*   $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=15";
        $api_data_prospects = $this->ApiCall($url);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/apprenants?codesPeriode=15";
        $api_data_apprenants = $this->ApiCall($url);

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/frequentes?codesPeriode=15";
        $api_data_frequentes = $this->ApiCall($url);  */




/*         
        //        /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        $url = "https://citeformations.ymag.cloud/index.php/r/v1//r/v1/formation-longue/prospects-with-events/@codeTypeEvt/8/@dateDebut/@dateFin/@evtClotures";
        $api_data_pr = $this->ApiCall($url);  

        dd($api_data_pr);
        foreach ($api_data_pr as $api_data_pr) {
            # code...
        }

 
                $codeApprenant =0;
                //$codeApprenant = 901581;
                //$codeApprenant = 989492;
                //$codeApprenant = 601034;
                //$codeApprenant = 845517;
                //$codeApprenant = 989492;
                foreach ($api_data_apprenants as $apprenant) {
                    if ($apprenant["codeApprenant"] ==  $codeApprenant ){
                        echo("Apprenant");
                        dump($apprenant);
        
                    } 
                }
                foreach ($api_data_frequentes as $frequente) {
                    if ($frequente["codeApprenant"] ==  $codeApprenant ){
                        echo("Frequente");
                        dump($frequente);
        
                    } 
                }
                foreach ($api_data_prospects as $prospect) {
                    if ($prospect["codeApprenant"] ==  $codeApprenant ){
                        echo("prospect");
                        dump($prospect);
        
                    }
                }
            //exit;

        foreach ($api_data_frequentes as $frequente) {

            $frequente_tab_complete[$frequente["codeApprenant"]] = array(
                "codeApprenant" => $frequente["codeApprenant"],
            );

            $date_fin = date_create_from_format('d/m/Y', $frequente["dateFin"]);
            $date_deb = date_create_from_format('d/m/Y', $frequente["dateDeb"]);
            if( (empty($frequente["dateFin"]) || $date_du_jour < $date_fin) && ($date_du_jour > $date_deb)) {
                //dd($frequente);
                $frequente_tab_netoyer[$frequente["codeApprenant"]] = array(
                    "codeApprenant" => $frequente["codeApprenant"],
                );
            }
        }
        //dump($frequente_tab_netoyer);

        foreach ($api_data_prospects as $prospect) {

            $codeEtape = $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 || $codeEtape == 149 || $codeEtape == 151 ) {     

                $prospects_tab[$prospect["codeApprenant"]] = array(
                    "codeApprenant" => $prospect["codeApprenant"]
                );

            }
        }


        foreach ($api_data_apprenants as $apprenant) {
                    
            for ($i=0; $i < count($apprenant["inscriptions"]) ; $i++) { 

                if ($apprenant["inscriptions"][$i]["isInscriptionEnCours"] == 1) {
                    $app_tab[$apprenant["codeApprenant"]] = array(
                        "codeApprenant" => $apprenant["codeApprenant"],
                        "nomAnnee" => $apprenant["inscriptions"][$i]["situation"]["nomAnnee"],
                        "nomFormation" => $apprenant["inscriptions"][$i]["formation"]["nomFormation"],
                    );
                }
            }

            if (!empty($prospects_tab[$apprenant["codeApprenant"]])) {
                //dump($apprenant);
            }

        }
        //dump($app_tab);



        $count_prospect_ok = 0;
        $count_prospect_non_ok = 0;
        foreach ($api_data_prospects as $prospect) {

            $codeEtape = $prospect["evenementsRacines"][0]["dernierEvenement"]["codeEtapeEvenement"];
            if ($codeEtape == 8 ) {     

                if (!empty($app_tab[$prospect["codeApprenant"]])) {

                    $count_prospect_ok++;

                    $prospects_tab_apprenant[$prospect["codeApprenant"]] = array(
                        "codeApprenant" => $prospect["codeApprenant"]
                    );

                    if(!empty($frequente_tab_netoyer[$prospect["codeApprenant"]])){
                        $count_prospect_non_ok++;
                    }else {
                        $prospects_tab_app[$prospect["codeApprenant"]] = array(
                            "codeApprenant" => $prospect["codeApprenant"]
                        );
                    }


    
                }else {
                    $prospects_tab_pas_apprenant[$prospect["codeApprenant"]] = array(
                        "codeApprenant" => $prospect["codeApprenant"]
                    );

                    
                }

            }
        }
        echo("ok");
        dump($count_prospect_ok);
        echo("non ok");
        dump($count_prospect_non_ok);
        dump($prospects_tab);
        echo("prospect dans tab apprenant");
        dump($prospects_tab_apprenant);
        //echo("prospect dans tab apprenant pas en frequentation");
        //dump($prospects_tab_app);
        dd($prospects_tab_pas_apprenant);

        //verifie que les apprenants dans de la table frequentation son bien dans la liste des apprenants
        $freq2= [];
        $count = 0;
        $count_apprenant_date_ok = 0;
        $count_apprenant_date_non_ok = 0;
        foreach ($api_data_frequentes as $frequente) {
    
            Array_push($freq2, $frequente["codeApprenant"] );

            $date_fin = date_create_from_format('d/m/Y', $frequente["dateFin"]);
            if(empty($frequente["dateFin"] || $date_du_jour < $date_fin) ){
                
                if (empty($app_tab[$frequente["codeApprenant"]])) {
                    $count_apprenant_date_ok++;
                    //dd($frequente["codeApprenant"]);
                }
                
            }else{
                $count++;
                if (empty($app_tab[$frequente["codeApprenant"]])) {
                    $count_apprenant_date_non_ok++;
                    //dd($frequente["codeApprenant"]);
                }
            }
        }
        echo("nombre de personne date ok mais pas dans la table apprenant");
        dump($count_apprenant_date_ok);//0
        echo("nombre de personne date pas ok et pas dans la table apprenant");
        dump($count_apprenant_date_non_ok);//0
        echo("nombre de personne qui on une date de fin inferieur a la date du jour");
        dump($count);
        echo("unique");
        dump($freq2);
        dump(array_count_values($freq2));


        $count = 0;
        $count_freq = 0;  
        foreach ($api_data_apprenants as $apprenant) {
                    
            $app_tab[$apprenant["codeApprenant"]] = array(
                "codeApprenant" => $apprenant["codeApprenant"],
            );

            //si l'apprenant et dans la table de frequentation
            if (!empty($prospects_tab_t[$apprenant["codeApprenant"]])) {     
                    $count++;
 
                   // dd($apprenant);
            }

            if (empty($frequente_tab_netoyer[$apprenant["codeApprenant"]])) {     
                $count_freq++;
                //dd($apprenant);

                
            }

        }
        echo("dans la table prospect est apprenant");
        dump($count);
        echo("dans la table apprenant mais pas dans la table frequente");
        dump($count_freq);
        //dump($app_tab);
        //dump($app_tab);
  

    exit;   */
 
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

   /*   
        //##############################################################################################
        //test performance table contrat
        //##############################################################################################
        
        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

            $url = "https://citeformations.ymag.cloud/index.php/r/v1/contrats";
            $api_data_contrats = $this->ApiCall($url);

        foreach ($api_data_contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            $date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );

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
        
        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo('contrat -> ');
        echo("temps execution : {$time}, Memoire utlisé : {$memory}");

        dump($contrats_tab);


        //requete contrat avec le code periode

        $contrats_tab = [];

        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

            $url = "https://citeformations.ymag.cloud/index.php/r/v1/contrats";
            $api_data_contrats = $this->ApiCall($url);

        foreach ($api_data_contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            $date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );

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
        
        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo('contrat -> ');
        echo("temps execution : {$time}, Memoire utlisé : {$memory}");

        dump($contrats_tab);


        //requete contrat avec la liste des apprenants comme filtre

        $contrats_tab = [];

        $d_microtime = microtime(true);
        $d_memory = memory_get_usage(true);

        $list_code_apprenant = [];
        foreach ($api_data_apprenants as $apprenant) {
            array_push($list_code_apprenant, $apprenant["codeApprenant"]);
        }
        $arr = implode(",",$list_code_apprenant);
        //dump($arr);
        //exit;

        $url = "https://citeformations.ymag.cloud/index.php/r/v1/contrats?codesApprenant={$arr}";
        $contrats = $this->ApiCall($url);

        foreach ($contrats as $contrat) {

            $date_fin = date_create_from_format('d/m/Y', $contrat["dateFinContrat"] ) ;
            $date_du_jour = date_create_from_format('d/m/Y', date("d/m/Y") );

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

        $a_microtime = microtime(true);
        $time = $a_microtime - $d_microtime;
        $a_memory = memory_get_usage(true);
        $memory = $a_memory - $d_memory;
        echo('contrat avec liste ->');
        echo("temps execution : {$time}, Memoire utlisé : {$memory}");

        
        dump($contrats_tab);
        exit;
        //#################################################################################################
       */


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

      /*  
        $unique = array_map("unserialize", array_unique(array_map("serialize", $frequente_tab)));
        dump($count);
        dump($unique);
        exit; */

        
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

        


        //recupere les secteur activite unique
        //$test = array_unique(array_column($final_tab, 'nomSecteurActivite'));

        //recupere un tableau et suprime tous les doublons
        $formation_secteur_tab = array_map("unserialize", array_unique(array_map("serialize", $formation_tab)));
        //re-index
        $formation_secteur_tab = array_values($formation_secteur_tab);
        //tri du trableau
        $columns_1 = array_column($formation_secteur_tab, 'nomSecteurActivite');
        $columns_2 = array_column($formation_secteur_tab, 'nomFormation');
        $columns_3 = array_column($formation_secteur_tab, 'nomAnnee');
        $formation_secteur_tab_tri =  array_multisort($columns_1, SORT_ASC, $columns_2, SORT_ASC, $columns_3, SORT_ASC, $formation_secteur_tab);
       


        $tab =  array (
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 2023',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 40,
            ),
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 2022',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 0,
            ),
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 MAI 2023',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 25,
            )
            );
 /*        dump($tab);
        //exit;
        echo "<pre>".print_r($formation_secteur_tab,true)."</pre>";
        exit;
        echo "<pre>";
        var_export($formation_secteur_tab);
        echo "</pre>";
        exit;
        echo "<pre>";
        print_r($formation_secteur_tab);
        echo "</pre>";

        exit;  */



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
