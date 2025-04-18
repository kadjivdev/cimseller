@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="pb-3">VALIDATION DE PROGRAMMATION</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programmations.index') }}">Listes des programmations</a></li>
                            <li class="breadcrumb-item active">Validation</li>
                        </ol>
                    </div>
                </div>
                @include('programmations.entete')
                @include('programmations.enteteproduit')
        </section>


        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-success bg-success">
                                <div class="card-body text-center">
                                    <h4>Voulez vous valider toutes les programmations du produit {{ $detailboncommande->produit->libelle }} bon de commande N° {{ $detailboncommande->boncommande->code }} ?</h4>
                                </div>
                                <div class="card-footer">
                                    <div class="row justify-content-center">
                                        <div class="col-sm-4"></div>
                                        <div class="col-sm-4 text-center">
                                            <form method="POST" action="{{ route('programmations.update',['detailboncommande'=>$detailboncommande->id, 'programmation'=>$programmation->id]) }}">
                                            @csrf
                                                <div class="row text-center">
                                                    <div class="col-md-6">
                                                        <a href="{{ route('programmations.index') }}" class="btn btn-sm btn-secondary  btn-block">
                                                            <i class="fa-solid fa-circle-left mr-1"></i>
                                                            {{ __('Retour') }}
                                                        </a>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="submit" class="btn btn-sm btn-primary  btn-block">
                                                            {{ __('Ok') }}
                                                            <i class="fa-solid fa-check ml-1"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-sm-4"></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
