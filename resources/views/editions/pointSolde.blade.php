@extends('layouts.app')
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="pb-3">EDITION - POINT DU SOLDE</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Accueil</a></li>
                        <li class="breadcrumb-item active">Point du Solde</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    @if(session()->has("message"))
                    <div class="alert alert-success">{{session()->get("message")}}</div>
                    @endif

                    @if(session()->has("error"))
                    <div class="alert alert-danger">{{session()->get("erro")}}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-body">
                            <form method="post" id="form_bc" action="{{ route('edition.postPointSolde') }}">
                                @csrf
                                <div class="row no-print">
                                    <div class="col-1"></div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="">Clients</label>

                                            <select id="client" class="form-control form-control-sm select2" name="client" required }}>
                                                <option class="" value="tous">Tous les clients</option>
                                                @foreach ($clients as $client)
                                                <option value="{{ $client->id }}" {{ old('client') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->raisonSociale }} ({{$client->id}})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="">Zone</label>
                                            <select id="zone" class="form-control form-control-sm select2" name="zone">
                                                <option class="text-center" value="" selected>Tous</option>
                                                @foreach ($zones as $zone)
                                                <option value="{{ $zone->id }}" {{ old('zone') == $zone->id ? 'selected' : '' }}>{{ $zone->libelle }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-primary" type="submit" style="margin-top: 2em">Afficher</button>
                                    </div>
                                    <div class="col-1"></div>
                                </div>
                            </form>

                            <!--  -->

                            @php($credit = session('resultat') ? session('resultat')['credit']: $credit)
                            @php($debit = session('resultat') ? session('resultat')['debit']: $debit)
                            @php($solde = $credit-$debit)

                            @php($realSolde = ($sommeVentes - $reglements)-$solde)

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">RESTE VENTE A PAYER</span>
                                            <span class="info-box-number" id='reste'>{{($sommeVentes - $reglements)?number_format(($sommeVentes - $reglements), '0', '', ' '):0}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="bi bi-dash-circle-fill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">DEBIT</span>
                                            <span class="info-box-number">{{number_format($debit, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-secondary"><i class="bi bi-plus-circle-fill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">CREDIT</span>
                                            <span class="info-box-number">{{number_format($credit, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">SOLDE</span>
                                            <span class="info-box-number" id="solde">{{number_format($solde, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(Auth::user()->roles()->where('libelle', 'ADMINISTRATEUR')->exists() == true || Auth::user()->roles()->where('libelle', 'CONTROLEUR')->exists() == true)
                            <div class="row d-flex">
                                <div class="col-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-coins"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">DETTE REELLE</span>
                                            <span class="info-box-number" id="realSolde">{{number_format($realSolde, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!session('resultat'))
                            <div class="row bg-dark pt-3">
                                <div class="col-md-6">
                                    <div class="info-box text-dark">
                                        <span class="info-box-icon bg-secondary"><i class="bi bi-plus-circle-fill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">SOLDE ANCIEN</span>
                                            <span class="info-box-number">{{number_format($credit_old, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box text-dark">
                                        <span class="info-box-icon bg-danger"><i class="bi bi-plus-circle-fill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">DETTE ANCIENNE</span>
                                            <span class="info-box-number">{{number_format($debit_old, '0', '', ' ')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <hr>
                            @if (session('resultat'))
                            <!-- ANCIEN COMPTE -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <span class="text-center bg-dark p-2"> <em> Solde de l'ancien compte du client</em> </span>
                                    </div>
                                    <br>
                                    <div class="row text-center">
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-dark" style="width: 50px!important;"><i class="bi bi-cash-coin"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Il nous devait:</span>
                                                            <span class="info-box-number">{{number_format(session('resultat')['client']['debit_old'], '0', '', ' ')}}</span>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-dark" style="width: 50px!important;"><i class="bi bi-cash-coin"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Nous lui devions </span>
                                                            <span class="info-box-number">{{number_format(session('resultat')['client']['credit_old'], '0', '', ' ')}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 text-center">
                                    <div class="text-center">
                                        <span class="text-center bg-danger p-2"> <em> TOTAL DU:</em> </span>
                                    </div>
                                    <br>
                                    <div class="info-box">
                                        <div class="info-box-content text-white bg-black">
                                            <strong class="info-box-text">Il nous doit au total: </strong>
                                            @if(Auth::user()->roles()->where('libelle', 'ADMINISTRATEUR')->exists() == true || Auth::user()->roles()->where('libelle', 'CONTROLEUR')->exists() == true)
                                            <input type="hidden" id="debit_old" value="{{-session('resultat')['client']['debit_old']}}">
                                            <span class="info-box-number" style="font-size: large" id="totalDu"></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @if (count(session('resultat')['ventes']) > 0)
                                <div class="col-md-12">
                                    <h4 class="col-12 text-center border border-info p-2 mb-2">
                                        Count: {{count(session('resultat')['ventes'])}}
                                        @if (session('resultat')['client'] != null)
                                        Point du solde de {{ session('resultat')['client']->raisonSociale }}
                                        @else
                                        Point du solde de la zone : {{ session('resultat')['zone']->libelle }}
                                        @endif
                                    </h4>

                                    @php($cpt = 0)
                                    @php($qte = 0)
                                    @php($montant = 0)
                                    @php($regle = 0)

                                    <!--  -->
                                    @if(count(session('resultat')['ventesDleted']) > 0)
                                    <div class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning my-1 text-center text-uppercase" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            <i class="bi bi-eye"></i> Ventes supprimées
                                        </button>
                                    </div>
                                    @endif

                                    <!--  -->
                                    <table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 11px">
                                        <thead class="text-white text-center bg-gradient-gray-dark">
                                            <tr>
                                                <th>Code</th>
                                                <th>Bl</th>
                                                <th>Passé le:</th>
                                                <th>date</th>
                                                <th>Chauf../Destin../Acteur</th>
                                                <th>Type</th>
                                                <th>Zones</th>
                                                <th>Quantite</th>
                                                <th>Montant</th>
                                                <th>Réglé</th>
                                                <th>Reste</th>
                                                <th>Echéance</th>
                                                <th>PR Code/Programmé par</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-body">
                                            @foreach (session('resultat')['ventes'] as $key => $item)

                                            @php($cpt++)
                                            @php($qte = $qte + $item->qteTotal)
                                            @php($montant = $montant + $item->montant)
                                            @php($regle = $regle + $item->reglements()->sum('montant'))
                                            <tr style="align-items: center!important;">
                                                <td>{{ $item->code }}</td>
                                                <td class="text-center font-weight-bold">
                                                    @if(count($item->vendus)>0)
                                                    @foreach ($item->vendus as $vendu )
                                                    <span class="badge bg-dark">{{ $vendu->programmation->bl_gest?$vendu->programmation->bl_gest:'--'}} / {{$vendu->programmation->bl?$vendu->programmation->bl:'--'}} </span>
                                                    @endforeach
                                                    @else
                                                    ---
                                                    @endif
                                                </td>
                                                <td class="text-center text-red"><span class="badge bg-dark text-white"> <i class="bi bi-calendar2-check-fill"></i> {{ \Carbon\Carbon::parse($item->created_at)->locale('fr')->isoFormat('D MMMM YYYY') }}</span> </td>

                                                <td>{{\Carbon\Carbon::parse(date_create($item->date))->locale('fr')->isoFormat('D MMMM YYYY')}}</td>
                                                <td>
                                                    @if(count($item->vendus)>0)
                                                    @foreach ($item->vendus as $vendu )
                                                    {{ $vendu->programmation->chauffeur->nom}} {{ $vendu->programmation->chauffeur->prenom}} / {{ $item->destination }}/ <span class="badge text-white bg-warning">{{$item->user->name}}</span>
                                                    @endforeach
                                                    @else
                                                    ---
                                                    @endif
                                                </td>
                                                <td>{{ $item->typeVente->libelle }}</td>
                                                <td>{{ $item->commandeclient->zone->libelle }}</td>
                                                <td>{{ number_format($item->qteTotal, '0', '', ' ') }}</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($item->montant, '0', '', ' ') }}
                                                </td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($item->reglements()->sum('montant'), '0', '', ' ') }}
                                                </td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($item->montant - $item->reglements()->sum('montant'), '0', '', ' ') }}
                                                </td>
                                                <td class="text-center font-weight-bold">
                                                    @if ($item->type_vente_id == 2)
                                                    @if ($item->montant - $item->reglements()->sum('montant') == 0)
                                                    <span class="badge bg-success"><i class="fa fa-check"></i> Soldé</span>
                                                    @elseif($item->echeances()->where('statut', 0)->first())
                                                    {{ date_format(date_create($item->echeances()->where('statut', 0)->first()->date),'d/m/Y') }}
                                                    @else
                                                    <span class="badge bg-danger"><i class="fa fa-times"></i> Non
                                                        défini</span> @endif
                                                    @elseif($item->montant - $item->reglements()->sum('montant') == 0)
                                                    <span class="badge bg-success"><i class="fa fa-check"></i> Soldé</span>
                                                    @else
                                                    <span class="badge bg-danger"><i class="fa fa-times"></i> Anomalie</span>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold">
                                                    @if(count($item->vendus)>0)
                                                    @foreach ($item->vendus as $vendu )
                                                    <span class="badge bg-dark">{{$vendu->programmation->code}} / {{$vendu->programmation->avaliseur->nom}} {{$vendu->programmation->avaliseur->prenom}} </span>
                                                    @endforeach
                                                    @else
                                                    ---
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="dropdown-item" href="{{route('reglements.index',['vente'=>$item])}}"><i class="fa-solid fa-file-invoice-dollar"></i> Règlement {{$item->id}} <span class="badge badge-info">{{$item->reglements ? count($item->reglements):0}}</span></a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>

                                    <h5 class="">Les totaux</h5>
                                    <table>
                                        <thead class="text-white text-center bg-gradient-gray-dark">
                                            <tr>
                                                <th>Quantite</th>
                                                <th>Montant</th>
                                                <th>Réglé</th>
                                                <th>Reste</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-center font-weight-bold">
                                                    {{ number_format($qte, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center font-weight-bold">
                                                    {{ number_format($montant, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center font-weight-bold">
                                                    {{ number_format($regle, 0, ',', ' ') }}
                                                </td>
                                                <td id="Tr" class="text-center font-weight-bold">
                                                    {{ number_format($montant - $regle, 0, ',', ' ') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="col-12 text-center border border-info p-2">
                                    Aucun stock trouvé pour votre requête.
                                </div>
                                <input type="hidden" id="montant_regle" value="0">
                                @endif
                            </div>

                            <!-- modal ventes supprimées -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content bg-transparent">
                                        @if (count(session('resultat')['ventesDleted']) > 0)

                                        <table class="table table-bordered table-striped table-sm" style="font-size: 12px">
                                            <thead class="text-white text-center bg-gradient-gray-dark">
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Date Vente</th>
                                                    <th>Date Validation</th>
                                                    <th>Type de Vente</th>
                                                    <th>Client</th>
                                                    <th>Qté</th>
                                                    <th>Transport</th>
                                                    <th>Total</th>
                                                    <th>Statut</th>
                                                    <th>Date de suppression</th>
                                                    <th>Montant</th>
                                                    <th>Reglement</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(session('resultat')['ventesDleted'] as $key=>$vente)
                                                <tr class="bg-warning">
                                                    <td class="text-center">{{ $vente->code }}</td>
                                                    <td class="text-center">{{ date_format(date_create($vente->date),'d/m/Y') }}</td>
                                                    <td class="text-center">{{$vente->validated_date? date_format(date_create($vente->validated_date),'d/m/Y'):"---" }}</td>
                                                    <td class="text-center">{{ GetVenteDeletedType($vente)}}</td>
                                                    <td class="text-center">{{ GetVenteDeletedClient($vente)}} </td>
                                                    <td class="text-center">{{ number_format($vente->qteTotal,0,'',' ') }}</td>
                                                    <td class="text-center">{{ number_format($vente->transport,0,'',' ') }}</td>
                                                    <td class="text-center">{{ number_format($vente->montant+$vente->transport,0,'',' ') }}</td>
                                                    <td class="text-center"><span class="badge badge-success">{{ $vente->statut }}</span></td>
                                                    <td class="text-center">{{ date_format(date_create($vente->created_at),'d/m/Y') }}</td>
                                                    <td class="text-center">{{ number_format($vente->montant,0,'',' ') }}</td>
                                                    <td class="texte-center">
                                                        @if($vente->reglement)
                                                        @if($vente->reglement!=0)
                                                        <span class="badge bg-success">Reglé</span>
                                                        @else
                                                        <span class="badge bg-danger">Non reglé</span>
                                                        @endif
                                                        @else
                                                        <span class="badge bg-light">---</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="text-white bg-dark">
                                                <tr>
                                                    <td colspan="10" class="font-weight-bold">Total </td>

                                                    <td colspan="3" class="text-left font-weight-bold">
                                                        {{ number_format(session('resultat')['ventesDleted']->sum("montant"),0,'',' ' )  }} FCFA
                                                    </td>

                                                </tr>
                                            </tfoot>
                                        </table>
                                        @else
                                        <div class="col-12 text-center border border-info p-2">
                                            Aucune vente supprimée du compte de ce client.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('script')

@if (session('resultat'))
<script>
    $(function() {
        // Récupérer les éléments
        var tdReste = $('#Tr');
        var spanReste = $('#reste');

        var spanSolde = $('#solde');
        var spanRealSolde = $('#realSolde');

        var totalDu = $('#totalDu');
        var debit_old = $('#debit_old');

        // Mettre la valeur initiale dans l'élément
        spanReste.html(tdReste.text() ? tdReste.text() : "00");

        // Déclencher la mise à jour de la valeur si besoin
        // (si la valeur de tdReste ne change pas dynamiquement)
        if (!tdReste.is(':input')) {
            tdReste.trigger('change');
        }

        // Attacher l'événement change à l'élément
        tdReste.on('change', function() {
            // Mettre la nouvelle valeur dans l'élément
            spanReste.html($(this));
        });

        var realSolde_amount = Number(spanReste.text().replace(/\s+/g, '')) - Number(spanSolde.text().replace(/\s+/g, ''))

        var __realeSold = new Intl.NumberFormat().format(realSolde_amount < 0 ? 00 : realSolde_amount)
        spanRealSolde.html(__realeSold ? __realeSold : "00")
        // console.log(Number(spanReste.text().replace(/\s+/g, '')))

        // var sommDu = Number($("#montant_regle").val()) + Number(debit_old.val())
        var sommDu = (realSolde_amount < 0 ? 0 : realSolde_amount) + Number(debit_old.val())
        var __sommDu = new Intl.NumberFormat().format(sommDu)
        totalDu.html(__sommDu ? __sommDu : "00")

    });

    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["pdf", "excel", "print"],
            // "order": [
            //     [1, 'asc']
            // ],
            "pageLength": 15,
            "columnDefs": [{
                    "targets": 2,
                    "orderable": false
                },
                {
                    "targets": 0,
                    "orderable": false
                }
            ],
            language: {
                "emptyTable": "Aucune donnée disponible dans le tableau",
                "lengthMenu": "Afficher _MENU_ éléments",
                "loadingRecords": "Chargement...",
                "processing": "Traitement...",
                "zeroRecords": "Aucun élément correspondant trouvé",
                "paginate": {
                    "first": "Premier",
                    "last": "Dernier",
                    "previous": "Précédent",
                    "next": "Suiv"
                },
                "aria": {
                    "sortAscending": ": activer pour trier la colonne par ordre croissant",
                    "sortDescending": ": activer pour trier la colonne par ordre décroissant"
                },
                "select": {
                    "rows": {
                        "_": "%d lignes sélectionnées",
                        "1": "1 ligne sélectionnée"
                    },
                    "cells": {
                        "1": "1 cellule sélectionnée",
                        "_": "%d cellules sélectionnées"
                    },
                    "columns": {
                        "1": "1 colonne sélectionnée",
                        "_": "%d colonnes sélectionnées"
                    }
                },
                "autoFill": {
                    "cancel": "Annuler",
                    "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                    "fillHorizontal": "Remplir les cellules horizontalement",
                    "fillVertical": "Remplir les cellules verticalement"
                },
                "searchBuilder": {
                    "conditions": {
                        "date": {
                            "after": "Après le",
                            "before": "Avant le",
                            "between": "Entre",
                            "empty": "Vide",
                            "equals": "Egal à",
                            "not": "Différent de",
                            "notBetween": "Pas entre",
                            "notEmpty": "Non vide"
                        },
                        "number": {
                            "between": "Entre",
                            "empty": "Vide",
                            "equals": "Egal à",
                            "gt": "Supérieur à",
                            "gte": "Supérieur ou égal à",
                            "lt": "Inférieur à",
                            "lte": "Inférieur ou égal à",
                            "not": "Différent de",
                            "notBetween": "Pas entre",
                            "notEmpty": "Non vide"
                        },
                        "string": {
                            "contains": "Contient",
                            "empty": "Vide",
                            "endsWith": "Se termine par",
                            "equals": "Egal à",
                            "not": "Différent de",
                            "notEmpty": "Non vide",
                            "startsWith": "Commence par"
                        },
                        "array": {
                            "equals": "Egal à",
                            "empty": "Vide",
                            "contains": "Contient",
                            "not": "Différent de",
                            "notEmpty": "Non vide",
                            "without": "Sans"
                        }
                    },
                    "add": "Ajouter une condition",
                    "button": {
                        "0": "Recherche avancée",
                        "_": "Recherche avancée (%d)"
                    },
                    "clearAll": "Effacer tout",
                    "condition": "Condition",
                    "data": "Donnée",
                    "deleteTitle": "Supprimer la règle de filtrage",
                    "logicAnd": "Et",
                    "logicOr": "Ou",
                    "title": {
                        "0": "Recherche avancée",
                        "_": "Recherche avancée (%d)"
                    },
                    "value": "Valeur"
                },
                "searchPanes": {
                    "clearMessage": "Effacer tout",
                    "count": "{total}",
                    "title": "Filtres actifs - %d",
                    "collapse": {
                        "0": "Volet de recherche",
                        "_": "Volet de recherche (%d)"
                    },
                    "countFiltered": "{shown} ({total})",
                    "emptyPanes": "Pas de volet de recherche",
                    "loadMessage": "Chargement du volet de recherche..."
                },
                "buttons": {
                    "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                    "collection": "Collection",
                    "colvis": "Visibilité colonnes",
                    "colvisRestore": "Rétablir visibilité",
                    "copy": "Copier",
                    "copySuccess": {
                        "1": "1 ligne copiée dans le presse-papier",
                        "_": "%ds lignes copiées dans le presse-papier"
                    },
                    "copyTitle": "Copier dans le presse-papier",
                    "csv": "CSV",
                    "excel": "Excel",
                    "pageLength": {
                        "-1": "Afficher toutes les lignes",
                        "_": "Afficher %d lignes"
                    },
                    "pdf": "PDF",
                    "print": "Imprimer"
                },
                "decimal": ",",
                "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
                "infoThousands": ".",
                "search": "Rechercher:",
                "thousands": ".",
                "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
                "datetime": {
                    "previous": "Précédent",
                    "next": "Suivant",
                    "hours": "Heures",
                    "minutes": "Minutes",
                    "seconds": "Secondes",
                    "unknown": "-",
                    "amPm": [
                        "am",
                        "pm"
                    ],
                    "months": [
                        "Janvier",
                        "Fevrier",
                        "Mars",
                        "Avril",
                        "Mai",
                        "Juin",
                        "Juillet",
                        "Aout",
                        "Septembre",
                        "Octobre",
                        "Novembre",
                        "Decembre"
                    ],
                    "weekdays": [
                        "Dim",
                        "Lun",
                        "Mar",
                        "Mer",
                        "Jeu",
                        "Ven",
                        "Sam"
                    ]
                },
                "editor": {
                    "close": "Fermer",
                    "create": {
                        "button": "Nouveaux",
                        "title": "Créer une nouvelle entrée",
                        "submit": "Envoyer"
                    },
                    "edit": {
                        "button": "Editer",
                        "title": "Editer Entrée",
                        "submit": "Modifier"
                    },
                    "remove": {
                        "button": "Supprimer",
                        "title": "Supprimer",
                        "submit": "Supprimer",
                        "confirm": {
                            "1": "etes-vous sure de vouloir supprimer 1 ligne?",
                            "_": "etes-vous sure de vouloir supprimer %d lignes?"
                        }
                    },
                    "error": {
                        "system": "Une erreur système s'est produite"
                    },
                    "multi": {
                        "title": "Valeurs Multiples",
                        "restore": "Rétablir Modification",
                        "noMulti": "Ce champ peut être édité individuellement, mais ne fait pas partie d'un groupe. ",
                        "info": "Les éléments sélectionnés contiennent différentes valeurs pour ce champ. Pour  modifier et "
                    }
                }
            },
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endif
@endsection