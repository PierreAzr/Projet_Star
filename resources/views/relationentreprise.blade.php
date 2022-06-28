
    @extends('layout')

    @section('title', 'Relation Entreprise Tableau')

    @section('content')
    <h1 class="center">Relation Entreprise Tableau</h1>

    <form method="get" action="{{ route('relation_entreprise_index') }}">
        <label for="start">Start date:</label>
    <input type="date" id="start" name="date" value= "{{ $date }}">
  <p><button>Submit</button></p>
    </form>


    <h1> Formation</h1>
    <form action="{{ route('previs_save_database') }}" method="POST">
            @csrf
    <table id="table_id" class="display">
        <thead>
            <tr>
                <th>Secteur</th>
                <th>Formation</th>
                <th>Années</th>
                <th>Envoi pré contrat</th>
                <th>Reception pré contrat</th>
                <th>Contrat recu</th>
                <th>nouveau inscrit</th>
                <th>inscrit N-1</th>
                <th>Total</th>
                <th>capacite Max</th>
                <th>Place Possible</th>
                <th>Previs</th>
                <th>Total avec previs</th>
                <th>Detail</th>
                <!-- <th>capaciteMax</th> -->

            </tr>
        </thead> 
        <!-- apprenant associatif -->
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
                    <a href="{{ route('AffichageFormation', ['formation' => $formation['nomFormation'],'annee' => $formation['nomAnnee']]) }}"></a>
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
                        <td><a href="{{ route('AffichageFormation', ['formation' => $formation['nomFormation'],'annee' => $formation['nomAnnee']]) }}">detail</a></td>   
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
    <input id="prodId" name="periode" type="hidden" value="{{ $periode_actuel }}">
            <input type="submit" value="Envoyer !">
            </form>
<script>
        $(document).ready( function () {
                $('#table_id').DataTable({

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