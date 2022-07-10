
@extends('layout')

@section('title', 'Relation Entreprise Tableau')

@section('content')

<h1 class="center">Effectifs sur la période {{ $periode_actuel }}</h1>
<br>

<br>
<!--Information, Choix date, Enregistrement previs -->
<div class="row">
    <div class="col-3 d-flex justify-content-center">
        <div>
        @if(isset($erreur))
        <a href="#erreur">
        <button class="btn btn-outline-danger" >Plusieurs Erreurs cliquez pour voir</button>
        </a>
        @endif
        </div>
    </div>
    <div class="col-3 d-inline-flex justify-content-end">
        <div>
            <div class="d-flex p-2 bg-Secondary text-white">{{ $periode_actuel }}</div>  
        </div>
        <div>
            @php($date_format = date_create_from_format('Y-m-d', $date )->format('d-m-Y'))
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">{{ $date_format }}</div> 
        </div>   
    </div >

    <div class="col-3 d-flex justify-content-start">
        <!--Debut Formulaire enregistrement-->
        <form method="get" action="{{ route('relation_entreprise_index') }}">
            <div class="input-group mb-3">
                @php($date_min = date('Y-m-d'))
                <input type="date" class="form-control"  min="{{ $date_min }}"  name="date" value="{{ $date }}">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
        </form>
    </div>

    <div class="col-3 d-flex justify-content-start">
        <div>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Enregistrer Previs</button>
        </div>
    </div>

 

</div>
<!--Fin-->

<hr>

<!-- KPI Total -->
<div class="row mb-10">
    <div class="col-12 mb-10 d-inline-flex justify-content-center">
   
            <div class="d-flex p-2 bg-Secondary text-white">Envoi pré contrat : {{ $total_tab["preContrat"] }}</div>  
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">Reception pré contrat : {{ $total_tab["receptionContrat"] }}</div>
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">Contrat recu : {{ $total_tab["contratRecu"] }}</div>
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">Nouveau inscrit : {{ $total_tab["nouveau"] }}</div>
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">inscrit N-1 : {{ $total_tab["ancient"] }}</div>
            <div class="d-flex p-2 ms-2 bg-Secondary text-white">Total : {{ $total_tab["total"] }}</div> 

    </div>
</div>
<!--Fin-->


<!-- Tableau effectif -->
<div class="row">
        
<!--Debut Formulaire enregistrement-->
<form name="previs_save" id="previs_save" action="{{ route('previs_save_database') }}" method="POST">
    @csrf
    <input id="prodId" name="periode" type="hidden" value="{{ $periode_actuel }}">
    <input id="prodId2" name="date" type="hidden" value="{{ $date }}">
    <br>
    <table id="table_id" class="display">
        <thead>
            <tr>
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
            <tr> 
                <td class="" style='background-color:   #aed6f1 '>{{ $formation["nomSecteurActivite"] }}</td>
                <td style='background-color:   #aed6f1 '>{{ $formation["nomFormation"] }}</td>
                <td style='background-color:   #aed6f1 '>{{ $formation["nomAnnee"] }}</td>
                <td style='background-color:   #ebf5fb   '>{{ $formation["preContrat"] }}</td>
                <td style='background-color:   #ebf5fb  '>{{ $formation["receptionContrat"] }}</td>
                <td style='background-color:   #ebf5fb  '>{{ $formation["contratRecu"] }}</td>
                <td style='background-color:    #d6eaf8'>{{ $formation["nouveau"] }}</td>
                <td style='background-color:   #d6eaf8 '>{{ $formation["ancient"] }}</td>
                <td style='background-color:   #aed6f1 '>{{ $formation["total"] }}</td>
                <td style='background-color: #fdedec'>{{ $formation["capaciteMax"] }}</td>
                <td style='background-color: #f5b7b1'>{{ $formation["nbPlacePossible"] }}</td>
                <td style='background-color:  #fcf3cf '>             
                    @if(!empty($formation["previ"]))
                        <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='{{ $formation["previ"] }}'>
                    @else
                        <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='0'>    
                    @endif
                </td>
                <td style='background-color:   #f9e79f  '>{{ $formation["previTotal"] }}</td>
                <td  style='background-color: ' >
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
                            <div class="modal-body">
                                <table id="table_{{ $formation['idFormation'] }}" class="display">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>PrénomApprenant</th>
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
                                        fixedHeader: {
                                                header: true,
                                                },
                                        language: {
                                            url: 'http:////cdn.datatables.net/plug-ins/1.12.1/i18n/fr-FR.json'
                                        },
                                        lengthMenu: [10, 50]
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Enregistrer Previs</button>
        </div>
    </div>
  

    <!-- Modal Enregistrement Formulaire -->
    <div class="modal fade" id="modalprevis" tabindex="-1" aria-labelledby="modalprevisLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalprevisLabel">Enregistrer Previs</h5>
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

<!-- Formation multiple et Erreur -->
<div class="row">
    <div class="">

        <br>
        <br>
        @if(isset($prospects_plusieurs_formation))
        <h2>Prospect avec plusieurs Formations :</h2>
        <br>
            <table class="table">
                <tr>
                    <th scope="col">Code Apprenant</th>
                    <th scope="col">Formation dans le tableau</th>
                </tr>
            @foreach($prospects_plusieurs_formation as $key => $formation)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $formation["nomFormation"] }} - {{ $formation["nomAnnee"] }}</td>
                </tr>
            @endforeach
            </table>
        @endif

        <br>
        @if(isset($erreur))

        <h2 id="erreur">Erreur apprenant exclu du tableau :</h2>
        <br>
            <table class="table">
                <tr>
                    <th scope="col">Erreur</th>
                    <th scope="col">Code Apprenant</th>
                </tr>
            @foreach($erreur as $key => $list)

                <tr>
                    <th scope="row">{{ $key }}</th>
                    <td>
                    @foreach($list as $key => $Apprenat)
                        {{ $Apprenat }},
                    @endforeach
                    </td>

                </tr>
            @endforeach
            </table>
        @endif

    </div>
</div>



<script>
    $(document).ready( function () {
        var table = $('#table_id').DataTable({
                fixedHeader: {
                         header: true,
                        },
                language: {
                    url: 'http:////cdn.datatables.net/plug-ins/1.12.1/i18n/fr-FR.json'
                },
                lengthMenu: [ [-1, 10, 25 ], ["Tous", 10, 25  ] ],

                
            });


        //pour resoudre le probleme d'enregistrement du formulaire
        $('#previs_save').on('submit', function(e){
        var form = this;

        // Encode a set of form elements from all pages as an array of names and values
        var params = table.$('input,select,textarea').serializeArray();

        // Iterate over all form elements
        $.each(params, function(){     
            // If element doesn't exist in DOM
            if(!$.contains(document, form[this.name])){
                // Create a hidden element 
                $(form).append(
                $('<input>')
                    .attr('type', 'hidden')
                    .attr('id', this.id)
                    .attr('name', this.name)
                    .val(this.value)
                );
            } 
        });      


        console.log(form);
        // Prevent actual form submission
        //e.preventDefault();
    });      

    

       /*  $('#button_sub').click( function () {
            let data = table.rows().data()
            console.log(data)
        } ); */


    });
</script>

@stop