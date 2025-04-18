@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>CLIENTS</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Acceuil</a></li>
                            <li class="breadcrumb-item active">liste des Clients</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            @if ($message = session('message'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert"
                                        aria-hidden="true">&times;</button>
                                    <h5><i class="icon fas fa-check"></i> Alert!</h5>
                                    {{ $message }}
                                </div>
                            @endif
                            @if ($message = session('error'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert"
                                        aria-hidden="true">&times;</button>
                                    <h5><i class="fa-solid fa-circle-exclamation"></i> Erreur!</h5>
                                    {{ $message }}
                                </div>
                            @endif
                            @if(Auth::user()->roles()->where('libelle', 'COMPTABLE')->exists()== false)                                                            
                            <div class="card-header">
                                <a href="{{ route('newclient.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-solid fa-plus"></i>
                                    Ajouter
                                </a>
                            </div>
                            @endif

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Nouveau Clients</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="{{ route('newclient.store') }}">
                                            @csrf
                                            <div class="modal-body">
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <div class="col-sm-4">
                                                    <a href="{{ route('clients.index') }}"
                                                        class="btn btn-sm btn-secondary btn-block">
                                                        <i class="fa-solid fa-circle-left mr-1"></i>
                                                        {{ __('Retour') }}
                                                    </a>
                                                </div>
                                                <div class="col-sm-4">
                                                    <button type="submit" class="btn btn-success btn-block">Enregistrer
                                                        <i class="fa-solid fa-floppy-disk"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <form action="" method="get">
                                <div class="row">
                                    <div class="col-1"></div>
                                    <div class="col-8">
                                        <div class="form-group m-1">
                                            <label for="">Recherche Client</label>
                                            <input type="text" class="form-control" name="search" value="{{old('search')}}" required>
                                        </div>
                                        @error('search')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                   
                                    <div class="col-2">
                                        <button class="btn btn-primary" type="submit" style="margin-top: 2em">Afficher</button>
                                    </div>
                                </div>
                            </form>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-striped table-sm"
                                    style="font-size: 12px">
                                    <thead class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Nom/Raison Sociale</th>
                                            <th>IFU</th>
                                            <th>Telephone</th>
                                            <th>Crédit</th>
                                            <th>MAJ</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @if ($clients->count() > 0)
                                            <?php $compteur = 1; ?>

                                            @foreach ($clients as $client)
                                                <tr>
                                                    <td>{{ $loop->index +1 }}</td>
                                                    <td class="ml-5 pr-5">{{ $client->raisonSociale ? $client->raisonSociale : $client->nom }}</td>
                                                    <td class="ml-5 pr-5">{{ $client->ifu }}</td>
                                                    <td class="ml-5 pr-5">{{ $client->telephone }}</td>
                                                    <td class="ml-5 pr-5 text-center">
                                                        <a href="{{ route('newclient.eligible', ['client' => $client->id]) }}"
                                                            class="btn ">
                                                            @if ($client->statutCredit == 1)
                                                                <span class="badge bg-success">Eligible</span>
                                                            @else
                                                                <span class="badge bg-secondary">Non Eligible</span>
                                                            @endif
                                                        </a>
                                                    <td class="text-center">
                                                        <!--<a class="btn btn-success btn-sm" href="#"><i class="fa-regular fa-eye"></i></a>-->
                                                        <a class="btn btn-secondary btn-sm"
                                                            href="{{ route('newclient.show', ['client' => $client->id]) }}"><i
                                                                class="fa-solid fa-eye"></i></a>
                                                     @if (Auth::user()->roles()->where('libelle', 'ADMINISTRATEUR')->exists())

                                                        <a class="btn btn-warning btn-sm"
                                                            href="{{ route('newclient.edit', ['client' => $client->id]) }}"><i
                                                                class="fa-solid fa-pen-to-square"></i></a>
                                                        <a class="btn btn-danger btn-sm"
                                                            href="{{ route('newclient.delete', ['client' => $client->id]) }}"><i
                                                                class="fa-solid fa-trash-can"></i></a>
                                                     @endif
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="dropdown-toggle btn btn-success btn-sm"
                                                                href="#" role="button" data-toggle="dropdown"
                                                                @if ($client->sommeil == true) disabled @endif>
                                                                Actions<i class="dw dw-more"></i>
                                                            </button>
                                                            <div
                                                                class="dropdown-menu dropdown-menu-md-right dropdown-menu-icon-list drop text-sm">
                                                                <a class="dropdown-item" href="{{ route('newclient.achatClient', ['client' => $client->id]) }}"><i
                                                                        class="nav-icon fa-solid fa-bag-shopping"></i>
                                                                    Achat</a>
                                                                <a class="dropdown-item" href=""><i 
                                                                        class="nav-icon fas fa-solid fa-basket-shopping"></i>
                                                                    Commande</a>
                                                                    
                                                                <a class="dropdown-item"
                                                                    href="{{ route('compteClient.show', ['client' => $client->id]) }}"><i
                                                                        class="nav-icon fa-solid fa-money-check-dollar"></i>
                                                                    Compte</a>

                                                                   @if(!Auth::user()->roles()->where('libelle', ['VALIDATEUR'])->exists() && !Auth::user()->roles()->where('libelle', ['SUPERVISEUR'])->exists())
                                                                    <a class="dropdown-item" target="_blank"
                                                                    href="{{ route('newclient.reglement', ['client' => $client->id]) }}"><i
                                                                        class="nav-icon fa-solid fa-money-check-dollar"></i>
                                                                    Règler dette</a>
                                                                    @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif 
                                    </tbody>
                                    <tfoot class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Raison Sociale</th>
                                            <th>IFU</th>
                                            <th>Telephone</th>
                                            <th>Crédit</th>
                                            <th>MAJ</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
        <!-- /.content -->
    </div>
@endsection
@section('script')
    <script>
        $(function() {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["pdf", "print"],
                "order": [
                    [0, 'asc']
                ],
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
@endsection
