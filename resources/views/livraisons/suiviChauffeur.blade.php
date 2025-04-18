@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="pb-3">SUIVIS CHAUFFEUR</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Accueil</a></li>
                            <li class="breadcrumb-item active">Suivi sortie</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                @if(session('transfert'))
                                    <div class="row">
                                        <div class="col-md-12 alert-success alert">
                                            <i class="fa fa-check"></i> Transfert de livraison effectué avec succès.
                                        </div>
                                    </div>
                                @endif
                                @if(session('message'))
                                    <div class="row">
                                        <div class="col-md-12 alert-success alert">
                                            <i class="fa fa-check"></i> {{session('message')}}
                                        </div>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="row">
                                        <div class="col-md-12 alert-dange alert">
                                            <i class="fa fa-check"></i> {{session('error')}}
                                        </div>
                                    </div>
                                @endif
                                <form id="statutsForm" action="{{route('livraisons.postSuivichauffeur')}}" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label for="">Début</label>
                                                        <input type="date" class="form-control" name="debut" value="{{old('debut')}}">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label for="">Fin</label>
                                                        <input type="date" class="form-control" name="fin" value="{{old('fin')}}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="prog" id="flexCheckDefault">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Par Prog Date
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="bon" id="flexCheckChecked">
                                                        <label class="form-check-label" for="flexCheckChecked">
                                                            Par Bon Date
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label for="">Chauffeurs</label>
                                                        <select class="custom-select form-control select2" id="chauffeur"
                                                                name="chauffeur">
                                                            <option value="tous" >Tous</option>
                                                            @foreach($chauffeurs as $chauffeur)
                                                                <option value="{{$chauffeur->id}}" @if(old('chauffeur') == $chauffeur->id) selected @endif>{{$chauffeur->nom}} {{$chauffeur->prenom}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label for="">Fournisseur</label>
                                                        <select class="custom-select form-control select2" id="fournisseur"
                                                                name="fournisseur">
                                                            <option value="tous" >Tous</option>
                                                            @foreach($fournisseurs as $fournisseur)
                                                                <option value="{{$fournisseur->id}}" @if(old('fournisseur') == $fournisseur->id) selected @endif>{{$fournisseur->raisonSociale}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label for="">Charger</label>
                                                        <select class="custom-select form-control" id="fournisseur"
                                                                name="option">
                                                            <option value="Tous" >Tous</option>
                                                            <option value="OUI" @if(old('option') == 'OUI') selected @endif>OUI</option>
                                                            <option value="NON" @if(old('option') == 'NON') selected @endif>NON</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 ">
                                                    <button class="btn btn-lg btn-primary" type="submit"
                                                            style="margin-top: 2em">Afficher
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- /.card-header -->

                            @if(session('resultat'))
                                <div class="alert alert-info m-1 text-center"><h4>{{session('messageReq')}}</h4></div>
                                @php($programmations = session('resultat'))
                                @php($request = session('request'))
                                <div class="card-body">
                                    <table id="example1" class="table table-bordered table-striped table-sm"
                                           style="font-size: 11px">
                                        <thead class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Code Prog</th>
                                            <th>Date Prog</th>
                                            <th>Date Bon</th>
                                            <th>Fournisseur</th>
                                            <th>Produit</th>
                                            <th>BL</th>
                                            <th>Camion</th>
                                            <th>Chauffeur</th>
                                            <th>Zone</th>
                                            <th>Qté</th>
                                            <th>Client et Destination</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table-body">
                                        @if ($programmations->count() > 0)
                                                <?php $compteur = 1; ?>
                                            @if($request['fournisseur'] == 'tous')
                                                @foreach($programmations as $programmation)
                                                    @php($dateDebut = new DateTime($programmation->dateprogrammer))
                                                    @php($dateFin = new DateTime(date('Y-m-d')))
                                                    @php($difference = $dateDebut->diff($dateFin))
                                                        <tr class="">
                                                        <td>{{ $compteur++ }}</td>
                                                        <td>{{ $programmation->detailboncommande->boncommande->code }}</td>
                                                        <td>{{ $programmation->code }}</td>
                                                        <td class="text-center">{{ $programmation->dateprogrammer?date_format(date_create($programmation->dateprogrammer), 'd/m/Y'):'' }}</td>
                                                        <td class="text-center">{{ $programmation->dateprogrammer?date_format(date_create($programmation->detailboncommande->boncommande->dateBon), 'd/m/Y'):'' }}</td>
                                                        <td>{{ $programmation->detailboncommande->boncommande->fournisseur->sigle }}</td>
                                                        <td>{{ $programmation->detailboncommande->produit->libelle }}</td>
                                                        <td>{{ $programmation->bl_gest?$programmation->bl_gest:$programmation->bl }}</td>
                                                        <td>{{ $programmation->camion->immatriculationTracteur }}
                                                            ({{ $programmation->camion->marque->libelle }})
                                                        </td>
                                                        <td>{{ $programmation->chauffeur->nom }} {{ $programmation->chauffeur->prenom }}
                                                            ({{ $programmation->chauffeur->telephone }})
                                                        </td>
                                                        <td>{{ $programmation->zone->libelle }}
                                                            ({{ $programmation->zone->departement->libelle }})
                                                        </td>
                                                        <td class="text-right">{{ number_format($programmation->qteprogrammer,2,","," ") }}</td>                                                      
                                                        <td>
                                                            @foreach ($programmation->vendus  as $vendu )
                                                                {{ $vendu->vente->commandeclient->client->raisonSociale }} - <b>({{$vendu->vente->destination}})</b> - <span style="color: green"><b>{{ $vendu->vente->qteTotal }}</b></span> <br>
                                                            @endforeach
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            @else

                                                @foreach($programmations as $programmation)
                                                    @php($dateDebut = new DateTime($programmation->dateprogrammer))
                                                    @php($dateFin = new DateTime(date('Y-m-d')))
                                                    @php($difference = $dateDebut->diff($dateFin))

                                                    @if($request['fournisseur'] == $programmation->detailboncommande->boncommande->fournisseur->id)
                                                        <tr class="">
                                                            <td>{{ $compteur++ }}</td>
                                                            <td>{{ $programmation->detailboncommande->boncommande->code }}</td>
                                                            <td>{{ $programmation->code }}</td>
                                                            <td class="text-center">{{ $programmation->dateprogrammer?date_format(date_create($programmation->dateprogrammer), 'd/m/Y'):'' }}</td>
                                                        <td class="text-center">{{ $programmation->dateprogrammer?date_format(date_create($programmation->detailboncommande->boncommande->dateBon), 'd/m/Y'):'' }}</td>
                                                            <td>{{ $programmation->detailboncommande->boncommande->fournisseur->sigle }}</td>
                                                            <!--<td>{{ $programmation->detailboncommande->produit->libelle }}</td>-->
                                                            <td>{{ $programmation->detailboncommande->produit->libelle }}</td>
                                                            <td>{{ $programmation->bl_gest?$programmation->bl_gest:$programmation->bl }}</td>
                                                            <td>{{ $programmation->camion->immatriculationTracteur }}
                                                                ({{ $programmation->camion->marque->libelle }})
                                                            </td>
                                                            <td>{{ $programmation->chauffeur->nom }} {{ $programmation->chauffeur->prenom }}
                                                                ({{ $programmation->chauffeur->telephone }})
                                                            </td>
                                                            <td>{{ $programmation->zone->libelle }}
                                                                ({{ $programmation->zone->departement->libelle }})
                                                            </td>
                                                            <td class="text-right">{{ number_format($programmation->qteprogrammer,2,","," ") }}</td>
                                                            <td>
                                                                @foreach ($programmation->vendus  as $vendu )
                                                                    {{ $vendu->vente->commandeclient->client->raisonSociale }}|<b>({{$vendu->vente->destination}})</b>|<span style="color: green"><b>{{ $vendu->vente->qteTotal }}</b></span> <br>
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endif
                                        </tbody>
                                        <tfoot class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Code Prog</th>
                                            <th>Date Prog</th>
                                            <th>Date Bon</th>
                                            <th>Fournisseur</th>
                                            <th>Produit</th>
                                            <th>BL</th>
                                            <th>Camion</th>
                                            <th>Chauffeur</th>
                                            <th>Zone</th>
                                            <th>Qté</th>
                                            <th>Client et Destination</th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                    <div class="modal fade" id="modal-default" style="display: none;"
                                         aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{route('livraisons.transfert')}}" method="post">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h4 class="modal-title text-center">Transfert de livraison</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row" id="loader">
                                                            <div class="col-md-12 text-center">
                                                                <i class="fa-spin spinner-border"></i><br>
                                                                Chargement...
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12 alert alert-danger" id="error" hidden>
                                                                <i class="fa fa-warning"></i> Une erreur interne est
                                                                survenue lors du chargement des données. Merci de
                                                                reprendre ou de contacter l'administrateur.
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2" id="form_modal" hidden>
                                                            <div class="col-md-5">
                                                                <label for="">Zone source</label>
                                                                <input type="hidden" name="id" id="id">
                                                                <input type="hidden" name="prog" id="prog">
                                                                <input type="text" disabled id="zone_souce"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-md-2 text-center">
                                                                <i class="fa fa-arrow-alt-circle-right fa-2x text-success"
                                                                   style="margin-top: 0.2em"></i>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label for="">Zone destination.</label>
                                                                <select name="zone_id" id="zone_dest"
                                                                        class="form-control" required></select>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 mb-2" id="motif" hidden>
                                                            <div class="col-md-12">
                                                                <label for="">Motif de transfert</label>
                                                                <textarea class="form-control" name="observation"
                                                                          required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-warning" id="warming" hidden>
                                                            <i class="fa fa-warning"></i> Le transfert de la livraison
                                                            est irreversible. Si vous êtes un résentant, cette livraison
                                                            quittera votre responsablité.
                                                            êtes vous sur de vouloir transferer cette livraison vers la
                                                            destination sélectionnée ?
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between" id="btn" hidden>
                                                        <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">Oui je confirme le
                                                            transfert
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="modal fade" id="modal-detail" style="display: none;" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title text-center">Détail transfert</h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="row p-3">
                                                    <div class="col-md-12">
                                                        <table class="table table-bordered" id="detailTransfert">
                                                            <tbody></tbody>

                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer justify-content-between" id="btn">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                                        Close
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">Oui je confirme le
                                                        transfert
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            @endif
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
    </div>
@endsection

@section('script')
    <script>
        function submitStatuts() {
            $('#statutsForm').submit();
        }


        function handleDateChange(id, inputElement) {
            const dateValue = inputElement.value;

            // Récupérer le conteneur du message
            const messageContainer = inputElement.parentNode.querySelector('.message-container');

            // Vérifier si la date est renseignée
            if (dateValue) {
                showLoader(messageContainer);
                // Appeler l'API via Axios
                axios.post('{{env('APP_BASE_URL')}}update-date-sortie/'+id, {
                    userName: '{{Auth::user()->name}}',
                    dateSortie: dateValue
                })
                    .then(function (response) {
                        // Afficher un message de succès
                        const successMessage = response.data
                        showMessage(messageContainer, '<i class="fas fa-check-circle"></i>', successMessage, 'text-success');
                    })
                    .catch(function (error) {
                        // Afficher un message d'erreur
                        const errorMessage = error.response.data;
                        showMessage(messageContainer, '<i class="fas fa-times-circle"></i>', errorMessage, 'text-danger',false);
                    });
            } else {
                // Si la date n'est pas renseignée, effacer le message
                messageContainer.innerHTML = '';
            }
        }

        function handleBordLivChange(id, inputElement) {
            const BLValue = inputElement.value;

            // Récupérer le conteneur du message
            const messageContainer = inputElement.parentNode.querySelector('.message-container-bl');

            // Vérifier si la date est renseignée
            if (BLValue) {
                showLoader(messageContainer);
                // Appeler l'API via Axios
                axios.get(`{{env('APP_BASE_URL')}}programmation/livraison/bl/${id}/${BLValue}/`+"{{Auth::user()->name }}" )
                    .then(function (response) {
                        // Afficher un message de succès
                        const successMessage = response.data
                      
                            showMessage(messageContainer, '<i class="fas fa-check-circle"></i>', successMessage, 'text-success');     
                     
                    })
                    .catch(function (error) {
                        // Afficher un message d'erreur
                        console.log( error.response.data);
                        const errorMessage = error.response.data;
                        showMessage(messageContainer, '<i class="fas fa-times-circle"></i>', errorMessage, 'text-danger',false);
                    });
            } else {
                // Si la date n'est pas renseignée, effacer le message
                messageContainer.innerHTML = '';
            }
        }

        function showLoader(container) {
            // Créer un élément pour le loader
            const loaderElement = document.createElement('div');
            loaderElement.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x font-weight-bold"></i>';
            loaderElement.classList.add('loader');

            // Ajouter le loader au conteneur
            container.innerHTML = '';
            container.appendChild(loaderElement);

            // Afficher le conteneur
            container.classList.remove('d-none');
        }

        function showMessage(container, iconHtml, message, textStyle, shouldDisappear = true) {
            // Créer un élément pour le message
            const messageElement = document.createElement('div');
            messageElement.innerHTML = `${iconHtml} ${message}`;
            messageElement.classList.add('message', textStyle);

            // Ajouter le message au conteneur
            container.innerHTML = '';
            container.appendChild(messageElement);

            // Afficher le conteneur
            container.classList.remove('d-none');

            // Si le message ne devrait pas disparaître, ne pas définir de minuteur
            if (shouldDisappear) {
                // Supprimer le message après quelques secondes
                setTimeout(function () {
                    container.innerHTML = '';
                    container.classList.add('d-none'); // Cacher le conteneur après avoir supprimé le message
                }, 3000);
            }
        }
        function loadProgrammation(id){
                $('#zone_dest option').remove()
                $('#zone_dest').removeAttr('selected');
                $('#loader').removeAttr('hidden');
                $('#form_modal').attr('hidden','hidden')
                $('#warming').attr('hidden','hidden')
                $('#btn').attr('hidden','hidden')
                $('#motif').attr('hidden','hidden')

                let optionsAsString = "<option value=''>--Sélectionnez la zone--</option>";
                axios.get('{{env('APP_BASE_URL')}}programmation/livraison/' + id).then((response) => {
                    console.log(response);
                    let zones = response.data.zones;
                    for(let i = 0; i < zones.length; i++) {
                        optionsAsString += "<option value='" + zones[i].id + "'>" + zones[i].libelle + "</option>";
                    }
                    $('#zone_dest' ).append( optionsAsString );
                    $('#id').val(response.data.programmation.zone_id)
                    $('#prog').val(response.data.programmation.id)
                    $('#zone_souce').val(response.data.zone_source)
                    $('#form_modal').removeAttr('hidden')
                    $('#warming').removeAttr('hidden')
                    $('#btn').removeAttr('hidden')
                    $('#motif').removeAttr('hidden')
                    $('#loader').attr('hidden','hidden')

                }).catch(()=>{
                    $('#loader').attr('hidden', 'hidden');
                    $('#error').removeAttr('hidden')
                })
        }

        function loadDetailTransfert(id){
                $('#detailTransfert > tbody > tr').remove();
                $('#loader1').removeAttr('hidden');
                axios.get('{{env('APP_BASE_URL')}}programmation/detail-transfert/' + id).then((response) => {
                    let details = response.data;
                    let table = document.getElementById("#detailTransfert");

                    ligneTableau = `
                         <tr>
                            <th>N°</th>
                            <th>Date</th>
                            <th>Zone source</th>
                            <th>Zone destination</th>
                            <th>Observation</th>
                        </tr>
                    `

                    //table.innerHTML = ligneTableau

                    $('#detailTransfert > tbody').append(ligneTableau);
                    for(let i = 0; i < details.length; i++) {
                        ligneTableau = `
                             <tr>
                                <td>${(details[i].compteur+1)}</td>
                                <td>${details[i].date}</td>
                                <td>${details[i].source}</td>
                                <td>${details[i].destination}</td>
                                <td>${details[i].observation}</td>
                            </tr>
                        `
                        $('#detailTransfert > tbody').append(ligneTableau);
                        /*optionsAsString += "<tr>"
                        optionsAsString += "<td>" + (details[i].compteur+1) + "</td>";
                        optionsAsString += "<td>" + details[i].date + "</td>";
                        optionsAsString += "<td>" +  + "</td>";
                        optionsAsString += "<td>" + details[i].destination + "</td>";

                        optionsAsString += "</tr>"*/
                    }


                    $('#loader1').attr('hidden','hidden')

                }).catch(()=>{
                    $('#loader1').attr('hidden', 'hidden');
                    $('#error1').removeAttr('hidden')
                })
        }          

        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["excel","pdf", "print"],
                "order": [
                    [1, 'asc']
                ],
                "pageLength": 100,
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
@endsection
