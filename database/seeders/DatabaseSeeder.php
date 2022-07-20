<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
//use App\Models\Formations;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        //DB::table('formations')->delete();



        /*           $data = $this->TableauFormation();
          foreach($data as $formation){
            Formations::create($formation);
        } */

        $data = $this->TableauFormation();
        foreach ($data as $formation) {
            DB::table('formations')->insert($formation);
        }

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@mail.fr',
            'password' => bcrypt(env('PASSWORD_ADMIN')),
        ]);
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@email.com',
            'password' => bcrypt('admin'),
        ]);
    }



    protected function TableauFormation()
    {

        return array(
            array(
                'nomSecteurActivite' => 'ASCENSEURS',
                'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 40,
            ),
            array(
                'nomSecteurActivite' => 'ASCENSEURS',
                'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 0,
            ),
            array(
                'nomSecteurActivite' => 'ASCENSEURS',
                'nomFormation' => 'TP TECHNICIEN DE TRAVAUX SUR ASCENSEUR',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 25,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'BAC PRO CARROSSERIE',
                'nomAnnee' => 'Première',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'BAC PRO CARROSSERIE',
                'nomAnnee' => 'Seconde',
                'capaciteMax' => 8,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'BAC PRO CARROSSERIE',
                'nomAnnee' => 'Terminale',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP PEINTRE EN CARROSSERIE',
                'nomAnnee' => 'en 1 an / sur 24 mois',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CAP REPARATION DES CARROSSERIES',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 20,
            ),
            /////////////////////////////////////////////////////
            array(
                'nomSecteurActivite' => 'AUTOMOBILE',
                'nomFormation' => 'CQP PEINTRE CONFIRME',
                'nomAnnee' => 'FC',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
                'nomAnnee' => 'Première',
                'capaciteMax' => 25,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
                'nomAnnee' => 'Seconde',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT A',
                'nomAnnee' => 'Terminale',
                'capaciteMax' => 25,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
                'nomAnnee' => 'Première',
                'capaciteMax' => 25,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
                'nomAnnee' => 'Seconde',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BAC PRO COMMERCE VENTE OPT B',
                'nomAnnee' => 'Terminale',
                'capaciteMax' => 25,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS MCO APP',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS MCO APP',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS MCO APP',
                'nomAnnee' => '2ème année redoublant',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS NDRC APP',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS NDRC APP',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'BTS NDRC CP',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 30,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 28,
            ),
            array(
                'nomSecteurActivite' => 'COMMERCE - VENTE',
                'nomFormation' => 'CAP EQUIPIER POLYVALENT DU COMMERCE',
                'nomAnnee' => 'en 1 an / sur 24 mois',
                'capaciteMax' => 28,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
                'nomAnnee' => '3ème année redoublant',
                'capaciteMax' => 12,
            ),

            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
                'nomAnnee' => 'Première',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
                'nomAnnee' => 'Seconde',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO COMM. SERVICE EN RESTAURATION',
                'nomAnnee' => 'Terminale',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO CUISINE',
                'nomAnnee' => 'Première',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO CUISINE',
                'nomAnnee' => 'Seconde',
                'capaciteMax' => 7,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BAC PRO CUISINE',
                'nomAnnee' => 'Terminale',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BP ARTS DE LA CUISINE',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 26,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BP ARTS DE LA CUISINE',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BP SERVICE ET COM. EN RESTAURATION',
                'nomAnnee' => '2ème année redoublant',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BTS MHR  OPT B',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BTS MHR  OPT B',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BTS MHR  OPT B',
                'nomAnnee' => '2ème année redoublant',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BTS MHR OPT A',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 12,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'BTS MHR OPT A',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 18,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CS HCR',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CS HCR',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 20,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CS HCR',
                'nomAnnee' => '2ème année redoublant',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CS HCR',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CS HCR',
                'nomAnnee' => 'en 1 an / sur 24 mois',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CUISINE',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 17,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CUISINE',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 28,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CUISINE',
                'nomAnnee' => '2ème année redoublant',
                'capaciteMax' => 28,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CUISINE',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP CUISINE',
                'nomAnnee' => 'en 1 an / sur 24 mois',
                'capaciteMax' => 14,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CAP PRODUCTION SERVICE RESTAURATION',
                'nomAnnee' => '2ème année',
                'capaciteMax' => 8,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'CQP RECEPTIONNISTE',
                'nomAnnee' => 'FC',
                'capaciteMax' => 15,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'MC ACCUEIL RECEPTION',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 16,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'MC CUISINIER EN DESSERTS DE RESTAURANT',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 16,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'MC EMPLOYE BARMAN',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 16,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'MC SOMMELLERIE',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 16,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'PREPA APP',
                'nomAnnee' => '1ère année',
                'capaciteMax' => 10,
            ),
            array(
                'nomSecteurActivite' => 'HOTELLERIE RESTAURATION',
                'nomFormation' => 'TITRE PRO CUISINIER',
                'nomAnnee' => 'en 1 an / sur 12 mois',
                'capaciteMax' => 12,
            ),
        );
    }
}
