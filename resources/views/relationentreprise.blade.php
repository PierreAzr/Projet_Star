
@extends('layout')

@section('title', 'Relation Entreprise Tableau')

@section('content')

<h1 class="center">Effectif periode {{ $periode_actuel }}</h1>
<br>

<br>
<div class="row">
<div class="col-3 d-inline-flex justify-content-end">

<div>
<div class="d-flex p-2 bg-Secondary text-white">{{ $periode_actuel }}</div>  

</div>
    
</div >
<div class="col-2 d-flex justify-content-center">
    <form method="get" action="{{ route('relation_entreprise_index') }}">
        <div class="input-group mb-3">
            <input type="date" class="form-control"  min="{{ $date }}" max='2025-01-01' name="date" value="{{ $date }}">
            <button class="btn btn-primary" type="submit">Go</button>
        </div>
    </form>
</div>
<div class="col-3 d-flex justify-content-center">
    <div>
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Enregistrer Previs</button>
    </div>
    
</div>
<div class="col-2 d-flex justify-content-center">

   
    <br>
</div>
</div>
<hr>

<div>


   

<div class="row">

<form name="previs_save" id="previs_save" action="{{ route('previs_save_database') }}" method="POST">
    @csrf
    <input id="prodId" name="periode" type="hidden" value="{{ $periode_actuel }}">
    <input id="prodId2" name="date" type="hidden" value="{{ $date }}">
    
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
                <th>inscrit N-1</th>
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
                <td>TOTAL</td>
                <td></td>
                <td></td>
                <td>{{ $total_tab["precontrat"] }}</td>
                <td>{{ $total_tab["receptioncontrat"] }}</td>
                <td>{{ $total_tab["contratrecu"] }}</td>
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
                <td>{{ $formation["nomSecteurActivite"] }}</td>
                <td>{{ $formation["nomFormation"] }}</td>
                <td>{{ $formation["nomAnnee"] }}</td>
                <td>{{ $formation["precontrat"] }}</td>
                <td>{{ $formation["receptioncontrat"] }}</td>
                <td>{{ $formation["contratrecu"] }}</td>
                <td>{{ $formation["nouveau"] }}</td>
                <td>{{ $formation["ancient"] }}</td>
                <td>{{ $formation["total"] }}</td>
                <td>{{ $formation["capaciteMax"] }}</td>
                <td>{{ $formation["nbPlacePossible"] }}</td>
                <td>
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
                <td>                          
                    {{ $previ_total }}
                </td>
                <td>
                

                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#modal{{ $formation['idFormation'] }}"><i class="bi bi-search"></i></button>


                    <!-- Modal -->
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
                                                            <td>Nouvel Apprenant</td>
                                                        @else
                                                            <td>Ancient</td>
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



                    <a href="{{ route('affichage_formation', ['formation' => $formation['nomFormation'],'annee' => $formation['nomAnnee'], 'date' => $date ]) }}">
                    <i class="bi bi-search"></i>
                    </a>
                </td>   
            </tr>
            @endforeach
            <tr>         
                <td>*TOTAL</td>
                <td></td>
                <td></td>
                <td>{{ $total_tab["precontrat"] }}</td>
                <td>{{ $total_tab["receptioncontrat"] }}</td>
                <td>{{ $total_tab["contratrecu"] }}</td>
                <td>{{ $total_tab["nouveau"] }}</td>
                <td>{{ $total_tab["ancient"] }}</td>
                <td>{{ $total_tab["total"] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table> 

</div>




<!-- Button trigger modal -->
<button type="button" id="button_sub" class="btn btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#modalprevis">Enregistrer Previs</button>


<!-- Modal -->
<div class="modal fade" id="modalprevis" tabindex="-1" aria-labelledby="modalprevisLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalprevisLabel">Enregistrer Previs</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            Enregistrer les previsions
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
            <button id="button_submit" class="btn btn-outline-primary" type="submit">Comfirmer</button>
        </div>
    </div>
    </div>
</div>

</form>
           
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