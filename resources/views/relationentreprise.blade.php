
    @extends('layout')

    @section('title', 'Relation Entreprise Tableau')

    @section('content')
    
    <h1 class="center">Effectif periode {{ $periode_actuel }}</h1>
    <br>

<br>
<div class="row">
    <div class="col-3 d-inline-flex justify-content-end">

    <div>
    <div class="d-flex p-2 bg-primary text-white">{{ $periode_actuel }}</div>  

    </div>
        
    </div >
    <div class="col-2 d-flex justify-content-center">
        <form method="get" action="{{ route('relation_entreprise_index') }}">
            <div class="input-group mb-3">
                <input type="date" class="form-control"  min="{{ $date }}" max='2023-01-01' name="date" value="{{ $date }}">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
        </form>
    </div>
    <div class="col-3 d-flex justify-content-center">

    </div>
    <div class="col-2 d-flex justify-content-center">

        <form action="{{ route('previs_save_database') }}" method="POST">
        @csrf
        <input id="prodId" name="periode" type="hidden" value="{{ $periode_actuel }}">
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalprevis">Enregister Previs</button>
        <br>
    </div>
</div>
<hr>

    <div class="row">
        <div class="" >
        <div class="" >
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
                        @if($previ->periode == '2021-2022' && $previ->idFormation == $formation['idFormation'])
                            <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='{{ $previ->previ }}'>
                            @php($previ_total = $formation["total"] + $previ->previ )
                        @endif
                    @endforeach  
                    @if(empty($previ_total)) 
                        <input size="1" type="text" name="{{ $formation['idFormation'] }}" id="{{ $formation['idFormation'] }}" value='0'>
                        @php($previ_total = $formation["total"])
                    @endif
                    </td>
                    <td>                          
                            {{ $previ_total }}
                    </td>
                    <td><a href="{{ route('affichage_formation', ['formation' => $formation['nomFormation'],'annee' => $formation['nomAnnee']]) }}"><i class="bi bi-search"></i></a></td>   
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
        </div>
    </div>
    <br> 



<!-- Button trigger modal -->
<button type="button" class="btn btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#modalprevis">
    Enregister Previs
</button>

<!-- Modal -->
<div class="modal fade" id="modalprevis" tabindex="-1" aria-labelledby="modalprevisLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalprevisLabel">Enregister Previs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Enregistrer les prevesions
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-outline-primary" type="submit">Comfirmer</button>
      </div>
    </div>
  </div>
</div>
</form>
               
<script>
        $(document).ready( function () {
                $('#table_id').DataTable({
                    fixedHeader: {
                             header: true,
                            },
                    language: {
                        url: 'http:////cdn.datatables.net/plug-ins/1.12.1/i18n/fr-FR.json'
                    },
                    lengthMenu: [10, 50]
                });
            } );




            $("document").ready(function(){
    setTimeout(function(){
       $("div.alert").remove();
    }, 5000 ); // 5 secs

});
    </script>

@stop