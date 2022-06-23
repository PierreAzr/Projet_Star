@extends('layout')

@section('title', 'Relation Entreprise Tableau')

@section('content')

<body>
    <h1> API TEST</h1>
    <h1>Formation</h1>


        <h1>{{ $formation }}{{ $annee }}</h1>


        <table id="table_id" class="display">
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
            @foreach ($tableau_complet_formation as $prospect)  
                <tr>
                    <td>{{ $prospect["nomApprenant"] }}</td>
                    <td>{{ $prospect["prenomApprenant"] }}</td>
                    @if(empty($prospect["nomStatut"]))
                        <td></td>
                    @else
                        <td>{{ $prospect["nomStatut"] }}</td>
                    @endif

                    @if(isset($prospect["nouveau"]))
                        @if($prospect["nouveau"] == 1)
                            <td>Nouvel Apprenant</td>
                        @else
                            <td>Ancient</td>
                        @endif
                    @else
                        <td></td>
                    @endif

                    @if(empty($prospect["nomEtapeEvenement"]))
                        <td></td>
                    @else
                        <td>{{ $prospect["nomEtapeEvenement"] }}</td>
                    @endif
                    @if(empty($prospect["nomEntreprise"]))
                        <td></td>
                    @else
                        <td>{{ $prospect["nomEntreprise"] }}</td>
                    @endif

                </tr>
            @endforeach
            </tbody>
        </table>


        <script>
        $(document).ready( function () {
                $('#table_id').DataTable({

                    language: {
                        url: 'http:////cdn.datatables.net/plug-ins/1.12.1/i18n/fr-FR.json'
                    },
                    lengthMenu: [100, 50]
                });
            } );
    </script>

 @stop

