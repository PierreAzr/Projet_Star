<?php

namespace App\Traits;

use Illuminate\Http\Request;

//pour utilise requete api
use Illuminate\Support\Facades\Http;
//mise en cache
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\RedirectResponse;

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
        
        //on test si la reponse et mauvaise
        if ($response->successful() ==  false) {

            if ($response->serverError()) {
                return redirect()->route('Welcome')->with('flash_message', "Une erreur s'est produite côté serveur sur une requête api; veuillez réessayer")
                ->with('flash_type', 'alert-danger');
            }elseif ($response->clientError()) {
                
                return redirect()->route('welcome')->with('flash_message', "Une erreur s'est produite de notre côté sur une requête api")
                ->with('flash_type', 'alert-danger')->send();
            }else{
                $response->throw();
            }
            
        }
        //var_dump($response->ok());
        // on decode le format json
        $data = json_decode($response, true);
        return $data;
        
    }


    // ****PERIODES
    protected function ApiPeriodes()
    {
        $api_data_periodes = Cache::get('api_data_periodes');
        $api_data_periodes = null;
        if (empty($api_data_periodes)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/periodes";
            $api_data_periodes = $this->ApiCall($url);
            Cache::put('api_data_periodes', $api_data_periodes, env('TEMP_CACHE_TRAITS') );
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
            Cache::put('api_data_formations', $api_data_formations, env('TEMP_CACHE_TRAITS'));
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
        $api_data_prospects = Cache::get('api_data_prospects');
        $api_data_prospects = null;
        if (empty($api_data_prospects)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events?codesPeriode=".$code_periode;
            $api_data_prospects = $this->ApiCall($url);
            Cache::put('api_data_prospects', $api_data_prospects, 32000);
        }
        return $api_data_prospects;
    }

    //Prospects (possibilite d'avoir seulement les prospects en cours)
    protected function ApiProspectsEvenement($codeTypeEvt, $codeEvenement, $dateDebut, $dateFin, $evtClotures )
    {

        // /r/v1/formation-longue/prospects-with-events/@codeTypeEvt/@codeEtapeEvt/@dateDebut/@dateFin/@evtClotures
        // codeTypeEvt trouver avec la table typeEvenement https://citeformations.ymag.cloud/index.php/r/v1/types-evenement
        $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/prospects-with-events/". $codeTypeEvt ."/".$codeEvenement."/".$dateDebut."/".$dateFin."/".$evtClotures;
        $api_data_prospect_evenement = $this->ApiCall($url);  

        return $api_data_prospect_evenement;
    }


    // ****CONTRATS
    protected function Apicontrats($code_periode = null)
    {  
        $api_data_contrats = Cache::get('api_data_contrats');
        //$api_data_contrats = null;
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
            Cache::put('api_data_entreprises', $api_data_entreprises, env('TEMP_CACHE_TRAITS'));
        }
        return $api_data_entreprises;
    }

    // ****GROUPES
    protected function ApiGroupes($code_periode=null)
    {    
        $api_data_groupes = Cache::get('api_data_groupes');
        $api_data_groupes = null;
        if (empty($api_data_groupes)) {
            $url = "https://citeformations.ymag.cloud/index.php/r/v1/formation-longue/groupes?codesPeriode=".$code_periode;
            $api_data_groupes = $this->ApiCall($url);
            Cache::put('api_data_groupes', $api_data_groupes, 32000);
        }
        return $api_data_groupes;
    }

  
}