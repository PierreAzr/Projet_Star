@extends('layout')

@section('title', 'Relation Entreprise Tableau')

@section('content')

<br>
<div class="row g-5">
      <div class="col-md-6">
        <h2>Mediation</h2>
        <p>Ready to beyond the starter template? Check out these open source projects that you can quickly duplicate to a new GitHub repository.</p>
        <ul class="icon-list">
          <li><a href="{{ route('relation_entreprise_index') }}" rel="noopener" >Tableau Effectifs</a></li>
        </ul>
      </div>

      <div class="col-md-6">
        <h2>Guides</h2>
        <p>Read more detailed instructions and documentation on using or contributing to Bootstrap.</p>
        <ul class="icon-list">
          <li><a href="/docs/5.0/getting-started/introduction/">Bootstrap quick start guide</a></li>
          <li><a href="/docs/5.0/getting-started/webpack/">Bootstrap Webpack guide</a></li>
          <li><a href="/docs/5.0/getting-started/parcel/">Bootstrap Parcel guide</a></li>
          <li><a href="/docs/5.0/getting-started/build-tools/">Contributing to Bootstrap</a></li>
        </ul>
      </div>
    </div>


@stop