<?php

namespace App\Traits;

use Illuminate\Http\Request;

//pour utilise requete api
use Illuminate\Support\Facades\Http;
//mise en cache
use Illuminate\Support\Facades\Cache;

trait ApiRequestTrait {

    function ApiCallCurl($url) {

        $token_api = env('TOKEN_YPAREO');
        $header_token = "X-Auth-Token";


        $curl = curl_init();

        // ATTENTION uniquement en LOCAL  permet de pas controler les certificats
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "{$header_token}: {$token_api}",
            'Content-Type: application/json',
        ));
         
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $result = curl_exec($curl);

        if($e = curl_error($curl)) {
            echo("erreur");
            echo $e;
        } else {
              
            // Decoding JSON data
            $decodedData = json_decode($result, true); 
                  
            // Outputing JSON data in
            // Decoded form
            //var_dump($decodedData);
        }

        curl_close($curl);
        return $decodedData;
    }

    protected function ApiCall($url) {

        echo("**ApiCall : {$url}");
        echo("********************");
        $token_api = env('TOKEN_YPAREO');
        $header_token = "X-Auth-Token";

        // ATTENTION uniquement en LOCAL withoutVerifying permet de pas controler les certificat
        $response = Http::withoutVerifying()
        ->withHeaders([$header_token => $token_api])
        ->withOptions(["verify"=>false])
        ->get($url);

        //Sur serveur
        //$response = Http::withHeaders([$header_token => $token_api])->get($url);

        //var_dump($response->ok());
        // on decode le format json
        $data = json_decode($response, true);

        return $data;
    }


    // ****PERIODES
    protected function ApiPeriodes()
    {
        $api_data_periodes = Cache::get('api_data_periodes');
        //$api_data_periodes = null;
        if (empty($api_data_periodes)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/periodes";
            $api_data_periodes = $this->ApiCall($url);
            Cache::put('api_data_periodes', $api_data_periodes, 32000);
        }

        return  $api_data_periodes;
    }

    // ****FORMATIONS
    protected function ApiFormations()
    {
        $api_data_formations = Cache::get('api_data_formations');
        //$api_data_formations = null;
        if (empty($api_data_formations)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formations";
            $api_data_formations = $this->ApiCall($url);
            Cache::put('api_data_formations', $api_data_formations, 32000);
        }

        return  $api_data_formations;
    }

    // ****APPRENANTS
    protected function ApiApprenants($code_periode=null)
    {
        // on recupere la variable dans le cache
        //$api_data_apprenants = Cache::get('api_data_apprenants');
        //$api_data_apprenants = null;
        //si elle est vide on lance la requete et on la met dans le chache
        if (empty($api_data_apprenants)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/apprenants?codesPeriode=".$code_periode;
            $api_data_apprenants = $this->ApiCall($url);
            //Cache::put('api_data_apprenants', $api_data_apprenants, 32000);
        }

        return $api_data_apprenants;
    }


    // ****FREQUENTES
    protected function ApiFrequentes($code_periode=null)
    {  
        //$api_data_frequentes = Cache::get('api_data_frequentes');
        $api_data_frequentes = null;
        if (empty($api_data_frequentes)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/apprenants/frequentes?codesPeriode=".$code_periode;
            $api_data_frequentes = $this->ApiCall($url);
            //Cache::put('api_data_frequentes', $api_data_frequentes, 32000);
        }
        return $api_data_frequentes;
    }


    // ****PROSPECTS
    protected function ApiProspects($code_periode=null)
    {
        //$api_data_prospects = Cache::get('api_data_prospects');
        $api_data_prospects = null;
        if (empty($api_data_prospects)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=".$code_periode;
            $api_data_prospects = $this->ApiCall($url);
            Cache::put('api_data_prospects', $api_data_prospects, 32000);
        }
        return $api_data_prospects;
    }


    // ****CONTRATS
    protected function Apicontrats($code_periode=null)
    {  
        $api_data_contrats = Cache::get('api_data_contrats');
       // $api_data_contrats = null;
        if (empty($api_data_contrats)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/contrats?codesPeriode=".$code_periode;
            $api_data_contrats = $this->ApiCall($url);
            Cache::put('api_data_contrats', $api_data_contrats, 32000);
        }
        return $api_data_contrats;
    }


    // ****ENTREPRISES
    protected function ApiEntreprises()
    {
        
        $api_data_entreprises = Cache::get('api_data_entreprises');
        //$api_data_entreprises = null;
        if (empty($api_data_entreprises)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/entreprises";
            $api_data_entreprises = $this->ApiCall($url);
            Cache::put('api_data_entreprises', $api_data_entreprises, 32000);
        }
        return $api_data_entreprises;
    }


    // ****GROUPES
    protected function ApiGroupes($code_periode=null)
    {
        
        //$api_data_groupes = Cache::get('api_data_groupes');
        $api_data_groupes = null;
        if (empty($api_data_groupes)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/groupes?codesPeriode=".$code_periode;
            $api_data_groupes = $this->ApiCall($url);
            Cache::put('api_data_groupes', $api_data_groupes, 32000);
        }
        return $api_data_groupes;
    }

    protected function TableauFormation()
    {
      
        array (
            0 => 
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 2023',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 40,
            ),
            1 => 
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 2022',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 0,
            ),
            2 => 
            array (
              'nomSecteurActivite' => 'ASCENSEURS',
              'nomGroupe' => 'ASC TP TTA 4 MAI 2023',
              'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 25,
            ),
            3 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'BAC CARR 2 2023',
              'nomFormation' => 'BAC PRO CARROSSERIE',
              'nomAnnee' => 'Première',
              'capaciteMax' => 20,
            ),
            4 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'BAC CARR 1 2024',
              'nomFormation' => 'BAC PRO CARROSSERIE',
              'nomAnnee' => 'Seconde',
              'capaciteMax' => 8,
            ),
            5 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'BAC CARR 2 2022',
              'nomFormation' => 'BAC PRO CARROSSERIE',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 20,
            ),
            6 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP PEINTRE 2B 2023',
              'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 12,
            ),
            7 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP PEINTRE 3B 2022',
              'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 10,
            ),
            8 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP PEINT NC 1 2022',
              'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 10,
            ),
            9 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP PEINT NC 1 2022',
              'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 10,
            ),
            10 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP CARROSSIER  2A 2023',
              'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 10,
            ),
            11 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP CARROSSIER 1 2023',
              'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 10,
            ),
            12 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP CARROSSIER 2 2022',
              'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            13 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CAP CARROSSIER 3A 2022',
              'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 10,
            ),
            14 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CQP PEINTRE CONFIRME G1 2022',
              'nomFormation' => 'CQP PEINTRE CONFIRME',
              'nomAnnee' => 'FC',
              'capaciteMax' => 12,
            ),
            15 => 
            array (
              'nomSecteurActivite' => 'AUTOMOBILE',
              'nomGroupe' => 'CQP PEINTRE CONFIRME G2 2022',
              'nomFormation' => 'CQP PEINTRE CONFIRME',
              'nomAnnee' => 'FC',
              'capaciteMax' => 12,
            ),
            16 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV A 2023',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Première',
              'capaciteMax' => 40,
            ),
            17 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV AB 2023',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Première',
              'capaciteMax' => 25,
            ),
            18 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC REST 2023',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Première',
              'capaciteMax' => 14,
            ),
            19 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV 1A 2024',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Seconde',
              'capaciteMax' => 20,
            ),
            20 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV A 2022',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 25,
            ),
            21 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV AB 2022',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 0,
            ),
            22 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV AB 2023',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
              'nomAnnee' => 'Première',
              'capaciteMax' => 25,
            ),
            23 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV A 2022',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 25,
            ),
            24 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BAC MCV AB 2022',
              'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 0,
            ),
            25 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS MCO (app) 2023',
              'nomFormation' => 'BTS MCO APP',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 20,
            ),
            26 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS MCO (app) 2022',
              'nomFormation' => 'BTS MCO APP',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            27 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS MCO (app) 2022',
              'nomFormation' => 'BTS MCO APP',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 20,
            ),
            28 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS NDRC (app) 2023',
              'nomFormation' => 'BTS NDRC APP',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 20,
            ),
            29 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS NDRC (app) 2022',
              'nomFormation' => 'BTS NDRC APP',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            30 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'BTS NDRC (cp) 2022',
              'nomFormation' => 'BTS NDRC CP',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            31 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'CAP EPC 1 2023',
              'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 30,
            ),
            32 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'CAP EPC 2 2023',
              'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 30,
            ),
            33 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'CAP EPC 1 2022',
              'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 28,
            ),
            34 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'CAP EPC 2 2022',
              'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 28,
            ),
            35 => 
            array (
              'nomSecteurActivite' => 'COMMERCE - VENTE',
              'nomGroupe' => 'CAP EPC 2 2022',
              'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 28,
            ),
            36 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC REST 2022',
              'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
              'nomAnnee' => '3ème année redoublant',
              'capaciteMax' => 12,
            ),
            37 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC REST 2023',
              'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
              'nomAnnee' => 'Première',
              'capaciteMax' => 14,
            ),
            38 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP REST 1 2023',
              'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
              'nomAnnee' => 'Première',
              'capaciteMax' => 12,
            ),
            39 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC REST 1B 2024',
              'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
              'nomAnnee' => 'Seconde',
              'capaciteMax' => 12,
            ),
            40 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC REST 2022',
              'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 12,
            ),
            41 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC CUIS 1A 2024',
              'nomFormation' => 'BAC PRO CUISINE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 7,
            ),
            42 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC CUIS 2023',
              'nomFormation' => 'BAC PRO CUISINE',
              'nomAnnee' => 'Première',
              'capaciteMax' => 14,
            ),
            43 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC CUIS 1A 2024',
              'nomFormation' => 'BAC PRO CUISINE',
              'nomAnnee' => 'Seconde',
              'capaciteMax' => 7,
            ),
            44 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BAC CUIS 2022',
              'nomFormation' => 'BAC PRO CUISINE',
              'nomAnnee' => 'Terminale',
              'capaciteMax' => 14,
            ),
            45 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP CUIS 1 2023',
              'nomFormation' => 'BP ARTS DE LA CUISINE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 26,
            ),
            46 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP CUIS 2 2022',
              'nomFormation' => 'BP ARTS DE LA CUISINE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 12,
            ),
            47 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP CUIS 3 2022',
              'nomFormation' => 'BP ARTS DE LA CUISINE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 12,
            ),
            48 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP REST 1 2023',
              'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 12,
            ),
            49 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP REST 2 2022',
              'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 12,
            ),
            50 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP REST 3 2022',
              'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 12,
            ),
            51 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BP REST 2 2022',
              'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 12,
            ),
            52 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BTS MHR B 2023',
              'nomFormation' => 'BTS MHR  OPT B',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 12,
            ),
            53 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BTS MHR B 2022',
              'nomFormation' => 'BTS MHR  OPT B',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 14,
            ),
            54 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BTS MHR B 2022',
              'nomFormation' => 'BTS MHR  OPT B',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 14,
            ),
            55 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BTS MHR A 2023',
              'nomFormation' => 'BTS MHR OPT A',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 12,
            ),
            56 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'BTS MHR A 2022',
              'nomFormation' => 'BTS MHR OPT A',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 18,
            ),
            57 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR 1 2023',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 20,
            ),
            58 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR 3 2023',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 20,
            ),
            59 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR 1 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            60 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR 2 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 20,
            ),
            61 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR NC 3 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 14,
            ),
            62 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR NC 2 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 14,
            ),
            63 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR NC 2 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 14,
            ),
            64 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR NC 3 2022',
              'nomFormation' => 'CAP CS HCR',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 14,
            ),
            65 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 1 2023',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 17,
            ),
            66 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 2 2023',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 28,
            ),
            67 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 3 2023',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 28,
            ),
            68 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 1 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 28,
            ),
            69 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 2 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 28,
            ),
            70 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 3 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 28,
            ),
            71 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 1 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 28,
            ),
            72 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 2 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => '2ème année redoublant',
              'capaciteMax' => 28,
            ),
            73 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUIS NC 2 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 14,
            ),
            74 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CSHCR 1 2023',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 20,
            ),
            75 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUIS NC 2 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 14,
            ),
            76 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUIS NC 3 2022',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 14,
            ),
            77 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP CUISINE 3 2023',
              'nomFormation' => 'CAP CUISINE',
              'nomAnnee' => 'en 1 an / sur 24 mois',
              'capaciteMax' => 28,
            ),
            78 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP PSR 2 2023',
              'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 10,
            ),
            79 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP PSR 3 2023',
              'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 10,
            ),
            80 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CAP PSR 1 2022',
              'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
              'nomAnnee' => '2ème année',
              'capaciteMax' => 8,
            ),
            81 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'CQP RECEPTION',
              'nomFormation' => 'CQP RECEPTIONNISTE',
              'nomAnnee' => 'FC',
              'capaciteMax' => 15,
            ),
            82 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'MC ACCUEIL RECEPTION 2 2022',
              'nomFormation' => 'MC ACCUEIL RECEPTION',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 16,
            ),
            83 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'MCCDR 1 2022',
              'nomFormation' => 'MC CUISINIER EN DESSERTS DE RESTAURANT',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 16,
            ),
            84 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'MC BARMAN 2 2022',
              'nomFormation' => 'MC EMPLOYE BARMAN',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 16,
            ),
            85 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'MC BARMAN 3 2022',
              'nomFormation' => 'MC EMPLOYE BARMAN',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 16,
            ),
            86 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'MC SOMMELIER 1 2022',
              'nomFormation' => 'MC SOMMELLERIE',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 16,
            ),
            87 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'PREPA APPRENTISSAGE CFAS',
              'nomFormation' => 'PREPA APP',
              'nomAnnee' => '1ère année',
              'capaciteMax' => 10,
            ),
            88 => 
            array (
              'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
              'nomGroupe' => 'TITRE PRO CUISINIER 2022',
              'nomFormation' => 'TITRE PRO CUISINIER',
              'nomAnnee' => 'en 1 an / sur 12 mois',
              'capaciteMax' => 12,
            ),
        );
            }


}