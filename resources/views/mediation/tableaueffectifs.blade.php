
@extends('layout')

@section('title', 'Tableau Effectifs')

@section('content')
<div class="row mb-5">
<h2 class="text-center fw-bold">Effectifs sur la période {{ $periode_actuel }}</h2>
</div>

<!--Information, Choix date, Enregistrement previs -->
<div class="row">
    <div class="col-3 offset-3 d-inline-flex justify-content-end">
        <div>
            <div class="d-flex p-2 bg-primary text-white">{{ $periode_actuel }}</div>  
        </div>
        <div>
            @php($date_format = date_create_from_format('Y-m-d', $date )->format('d-m-Y'))
            <div class="d-flex p-2 ms-2 bg-primary text-white">{{ $date_format }}</div> 
        </div>   
    </div >

    <div class="col-3 d-flex justify-content-start">
        <!--Debut Formulaire enregistrement-->
        <form method="get" action="{{ route('mediation_tableau_effectifs') }}">
            <div class="input-group mb-3">
                @php($date_min = date('Y-m-d'))
                <input type="date" class="form-control"  min="{{ $date_min }}"  name="date" value="{{ $date }}">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
        </form>
    </div>

    <div class="col-3 d-flex justify-content-start">
        <div>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Sauvegarder prévisionnels</button>
        </div>
    </div>

 

</div>
<!--Fin-->

<hr>


<!-- Tableau Erreur -->
@if(!empty($erreur))
<div >
    <br>
    <div class="card border-danger">
        <div class="card-header text-white bg-danger">
            <h3 id="erreur">Erreur apprenant exclu du tableau</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tr>
                    <th scope="col">Erreur</th>
                    <th scope="col">Code Apprenant</th>
                </tr>
                @foreach($erreur as $key => $list)
                <tr>
                    <th scope="row">
                        @if( $key  == "liste_annee_null")
                        <p>Prospect avec un nom d'année null</p>
                        
                        @endif
                        @if( $key  == "liste_annee_mauvaise")
                        <p>Le nom d'année n'est pas associé a la formation dans le tableau*</p> 
                        @endif
                        @if( $key  == "liste_formation_existe_pas")
                        <div>Le nom de la formation n'est pas dans le tableau* </div>
                        @endif
                    </th>
                    <td>
                    @foreach($list as $key => $Apprenat)
                        {{ $Apprenat }},
                    @endforeach
                    </td>
                </tr>
                @endforeach
            </table>
            <span class="fw-lighter" >* Si nouvelle formation ou nouveau nom d'année, contacter le service informatique</span>
        </div>
    </div>

</div>
<hr>
<br>
@endif

<!-- KPI Total -->
<div class="row mb-10">
    <div class="col-12 mb-10 d-inline-flex justify-content-center">
   
            <div class="d-flex p-2 border border-dark couleurtableaueffectis2">Envoi pré contrat : {{ $total_tab["preContrat"] }}</div>  
            <div class="d-flex p-2 ms-2 border border-dark couleurtableaueffectis2">Reception pré contrat : {{ $total_tab["receptionContrat"] }}</div>
            <div class="d-flex p-2 ms-2 border border-dark couleurtableaueffectis2">Contrat recu : {{ $total_tab["contratRecu"] }}</div>
            <div class="d-flex p-2 ms-2 border border-dark couleurtableaueffectis3">Nouveau inscrit : {{ $total_tab["nouveau"] }}</div>
            <div class="d-flex p-2 ms-2 border border-dark couleurtableaueffectis3">inscrit N-1 : {{ $total_tab["ancient"] }}</div>
            <div class="d-flex p-2 ms-2  border border-dark couleurtableaueffectis1">Total : {{ $total_tab["total"] }}</div> 

    </div>
</div>
<!--Fin-->


<!-- Tableau effectif -->
<div class="row">
        
<!--Debut Formulaire enregistrement-->
<form name="previs_save" id="previs_save" action="{{ route('mediation_previs_save_database') }}" method="POST">
    @csrf
    <input id="prodId" name="periode" type="hidden" value="{{ $periode_actuel }}">
    <input id="prodId2" name="date" type="hidden" value="{{ $date }}">
    <br>
    <table id="table_id" class="table">
        <thead class="table-bordered">
            <tr >
                <th>Secteurs</th>
                <th>Formations</th>
                <th>Années</th>
                <th>Envoi pré contrat</th>
                <th>Reception pré contrat</th>
                <th>Contrat recu</th>
                <th>Nouveau inscrit</th>
                <th>Inscrit N-1</th>
                <th>Total</th>
                <th>Capacité Max</th>
                <th>Place Possible</th>
                <th>Previs</th>
                <th>Total avec previs</th>
                <th>Detail</th>
            </tr>
        </thead> 
        <tbody>
            <tr>         
                <td>*TOTAL</td>
                <td></td>
                <td></td>
                <td>{{ $total_tab["preContrat"] }}</td>
                <td>{{ $total_tab["receptionContrat"] }}</td>
                <td>{{ $total_tab["contratRecu"] }}</td>
                <td>{{ $total_tab["nouveau"] }}</td>
                <td>{{ $total_tab["ancient"] }}</td>
                <td>{{ $total_tab["total"] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @foreach ($final_tab as $formation)
            <tr scope="row"> 

                <td scope="col" class="couleurtableaueffectis1">{{ $formation["nomSecteurActivite"] }}</td>
                <td scope="col"class="couleurtableaueffectis1">{{ $formation["nomFormation"] }}</td>
                <td class="couleurtableaueffectis1">{{ $formation["nomAnnee"] }}</td>
                <td class="couleurtableaueffectis2">{{ $formation["preContrat"] }}</td>
                <td class="couleurtableaueffectis2">{{ $formation["receptionContrat"] }}</td>
                <td class="couleurtableaueffectis2">{{ $formation["contratRecu"] }}</td>
                <td class="couleurtableaueffectis3">{{ $formation["nouveau"] }}</td>
                <td class="couleurtableaueffectis3">{{ $formation["ancient"] }}</td>
                <td class="couleurtableaueffectis1">{{ $formation["total"] }}</td>
                <td class="couleurtableaueffectis4">{{ $formation["capaciteMax"] }}</td>
                <td class="couleurtableaueffectis5">{{ $formation["nbPlacePossible"] }}</td>
                <td class="couleurtableaueffectis6">             
                @php($previ_total = null )
                @foreach($previs as $previ)
                    @if($previ->periode == $periode_actuel && $previ->idFormation == $formation['idFormation'])
                        <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='{{ $previ->previ }}'>
                        @php($previ_total = $formation["total"] + $previ->previ )
                    @endif
                @endforeach  
                @if(!isset($previ_total)) 
                    <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='0'>
                    @php($previ_total = $formation["total"])
                @endif
                </td>
                <td class="couleurtableaueffectis7">{{ $previ_total }}</td>
                <td >

                    <!-- Modal pour afficher le detail d'une formation -->

                    <!-- Button modal Detail-->
                    <button type="button" class="btn btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#modal{{ $formation['idFormation'] }}"><i class="bi bi-search"></i></button>


                    <!-- Modal Detail-->
                    <div class="modal fade" id="modal{{ $formation['idFormation'] }}" tabindex="-1" aria-labelledby="modalprevis{{ $formation['idFormation'] }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalprevis{{ $formation['idFormation'] }}Label">{{ $formation["nomFormation"] }} {{ $formation["nomAnnee"] }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body table-responsive">
                                <table id="table_{{ $formation['idFormation'] }}" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Groupe</th>
                                                <th>Statut</th>
                                                <th>Nouveau</th>
                                                <th>Evenement</th>
                                                <th>Entreprise</th>
                                            </tr>
                                        </thead>
                                        <tbody> 
                                        @foreach($tableau_complet as $individu)
                                            @if ($individu['nomFormation'] == $formation["nomFormation"] && $individu["nomAnnee"] == $formation["nomAnnee"]) 

                                                <tr>
                                                    <td>{{ $individu["nomApprenant"] }}</td>
                                                    <td>{{ $individu["prenomApprenant"] }}</td>
                                                    @if(empty($individu["nomGroupe"]))
                                                        <td></td>
                                                    @else
                                                        <td>{{ $individu["nomGroupe"] }}</td>
                                                    @endif
                                                    @if(empty($individu["nomStatut"]))
                                                        <td></td>
                                                    @else
                                                        <td>{{ $individu["nomStatut"] }}</td>
                                                    @endif

                                                    @if(isset($individu["nouveau"]))
                                                        @if($individu["nouveau"] == 1)
                                                            <td>Nouveau inscrit</td>
                                                        @else
                                                            <td>Inscrit N-1</td>
                                                        @endif
                                                    @else
                                                        <td></td>
                                                    @endif

                                                    @if(empty($individu["nomEtapeEvenement"]))
                                                        <td></td>
                                                    @else
                                                        <td>{{ $individu["nomEtapeEvenement"] }}</td>
                                                    @endif
                                                    @if(empty($individu["nomEntreprise"]))
                                                        <td></td>
                                                    @else
                                                        <td>{{ $individu["nomEntreprise"] }}</td>
                                                    @endif

                                                </tr>

                                            @endif
                                        @endforeach
                                        </tbody>
                                </table>

                            </div>

                        </div>
                        </div>
                    </div>
                     <!-- Sript datatable pour le detail d'une formation -->
                    <script>
                            $(document).ready( function () {
                                    $("#table_{{ $formation['idFormation'] }}").DataTable({
                                        language: {
                                            url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/fr-FR.json'
                                        },
                                        lengthMenu:[ [  10 , -1], [ 10, "Tous"  ] ],
                                    });
                                } );
                    </script>

                </td> 
                <!-- FIN Modal pour afficher le detail d'une formation --> 
            </tr>
            @endforeach
        </tbody>
    </table> 
    <br>


    <!-- Button modal Enregistrement-->
    <div class="col-12 d-flex justify-content-center">
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Sauvegarder prévisionnels</button>
        </div>
    </div>
  

    <!-- Modal Enregistrement Formulaire -->
    <div class="modal fade" id="modalprevis" tabindex="-1" aria-labelledby="modalprevisLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalprevisLabel">Sauvegarder prévisionnels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <p>ATTENTION !!! Avant d'enregistrer les previsions.</p> 
               <p>Veuiller a ce que :</p>
                                <ul>
                                <li>le nombre entrée soit sur "Tous"</li>
                    <li>le champ rechercher soit vide</li>
                    </ul>
               
               <p>L'enregistrement ne ce fait que sur le donnée que le tableau affiche.</p> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                <button id="button_submit" class="btn btn-outline-primary" type="submit">Comfirmer</button>
            </div>
        </div>
        </div>
    </div>

</form>
<!--Fin Formulaire enregistrement-->

</div>
<!--Fin Tableau effectifs-->

<hr>

<!-- Formation multiple, apprenants et prospects -->
<div class="row">
    <div class="accordion" id="accordionExample">
        @if(!empty($prospects_plusieurs_formation))   
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
            Prospects avec plusieurs Formations ({{count($prospects_plusieurs_formation)}})
            </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                   
                <table class="table" >
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th scope="col">Formation dans le tableau (si pas erreur)</th>
                        <th scope="col">Autres choix de formation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prospects_plusieurs_formation as $key => $formation)
                    <tr>
                        <td>{{ $formation["nomApprenant"] }}</td>
                        <td>{{ $formation["prenomApprenant"] }}</td>
                        <td>{{ $formation["nomFormation0"] }} - {{ $formation["nomAnnee0"] }}</td>
                        <td>
                            @for($i=1; $i < (count($formation)/2 - 1) ; $i++)                             
                                {{ $formation["nomFormation$i"] }} - {{ $formation["nomAnnee$i"] }};
                            @endfor            
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table> 

                          
            </div>
            </div>     
        </div>
        @endif 

        @if(!empty($commun_tab))
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Apprenant et Prospect à la fois ({{count($commun_tab)}})
                
            </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                
            <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Formation (Apprenant)</th>
                            <th>Année (Apprenant)</th>
                            <th>Evènement (Prospect)</th>

                        </tr>
                    </thead>
                    <tbody> 
                    @foreach($commun_tab as $individu) 
                        <tr>
                            <td>{{ $individu["nomApprenant"] }}</td>
                            <td>{{ $individu["prenomApprenant"] }}</td>
                            <td>{{ $individu["nomFormation"] }}</td>
                            <td>{{ $individu["nomAnnee"] }}</td>
                            <td>{{ $individu["nomEtapeEvenement"] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
            </div>
        </div>
        @endif

    </div>
</div>
   
<br>

<script>
    $(document).ready( function () {
        var table = $('#table_id').DataTable({
            //dom: 'Blfrtip',
                //buttons: ['pdf', 'print'], 
  
                language: {
                    url: cdn_fr_datatable
                },
                lengthMenu: [ [-1, 10, 25 ], ["Tous", 10, 25  ] ],

                
            });

    });      

    
</script>

@stop