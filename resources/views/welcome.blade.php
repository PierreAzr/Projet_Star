@extends('layout')

@section('title', 'Relation Entreprise Tableau')

@section('content')

<br>
<div class="row">
    <div class="col-12 d-flex justify-content-center">
        <div>
        <img src="{{ asset('img/star_logo.png') }}" alt="logo">
        </div>
    </div>
  </div>
  <br>



    <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3 ">
            <h4 class="my-0 fw-bold">Médiation</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
              <li><a class="link-dark" href="{{ route('mediation_tableau_effectifs') }}">Tableau Effectifs</a></li>
              <li><a class="link-dark" href="{{ route('mediation_rupture_contrat') }}">Rupture Contrat</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3">
            <h4 class="my-0 fw-bold">Education</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
            <li><a class="link-dark" href="{{ route('eductaion_welcome') }}">A venir</a></li>
            <li><a class="link-dark" href="{{ route('eductaion_welcome') }}">A venir</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3">
            <h4 class="my-0 fw-bold">Examens</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
            <li><a class="link-dark" href="{{ route('examens_welcome') }}">A venir</a></li>
         
            </ul>
          </div>
        </div>
      </div>
    </div>
    <br>
    <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3 ">
            <h4 class="my-0 fw-bold">Ressources Humaines</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
            <li><a class="link-dark" href="{{ route('ressources_humaines_welcome') }}">A venir</a></li>
            <li><a class="link-dark" href="{{ route('ressources_humaines_welcome') }}">A venir</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3">
            <h4 class="my-0 fw-bold">Comptabilité</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
            <li><a class="link-dark" href="{{ route('comptabilite_welcome') }}">A venir</a></li>

            </ul>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm h-100">
          <div class="card-header py-3 ">
            <h4 class="my-0 fw-bold">Pédagogie</h4>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mt-3 mb-4">
            <li><a class="link-dark" href="{{ route('pedagogie_welcome') }}">A venir</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>


@stop