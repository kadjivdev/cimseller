@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Recouvrement clients</h1>
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
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

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

                        <div class="card-header">
                            <a data-bs-toggle="modal" data-bs-target="#addRecouvrement" class="btn btn-success btn-sm">
                                <i class="fas fa-solid fa-plus"></i>
                                Ajouter
                            </a>
                        </div>

                        <br>
                        <!-- RECHERCHER AR CLIENT -->
                        <div class="row justify-content-center d-flex">
                            <div class="col-md-6">
                                <form class="border  p-3 rounded bg-light" action="{{route('recouvrement.index')}}" method="get">
                                    <select name="client" class="form-control form-select select2" required>
                                        <option value="">Selectionnez un client</option>
                                        @foreach($clients as $client)
                                        <option
                                            value="{{$client->id}}">{{$client->raisonSociale}}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error("client")
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="">
                                        <button class="w-100 mt-3 btn btn-primary" type="submit">Filtrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- AJOUT DE RECOUVREMENT -->
                        <div class="modal fade" id="addRecouvrement">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Nouveau Recouvrement</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('recouvrement.store') }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group mb-3">
                                                <select name="client_id" class="form-control form-select select2" required>
                                                    <option value="">Selectionnez un client</option>
                                                    @foreach($clients as $client)
                                                    <option value="{{$client->id}}">{{$client->raisonSociale}}</option>
                                                    @endforeach
                                                </select>
                                                @error("client")
                                                <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                            <div class="mb-3 form-group">
                                                <textarea name="comments" class="form-control" rows="3" id="" placeholder="Commentaire ...." required></textarea>
                                                @error("comments")
                                                <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="submit" class="w-100 btn btn-success btn-block">Enregistrer
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- FIN RECOUVREMENT -->

                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="{{route('recouvrement.verification')}}" method="post">
                                <!--  -->
                                @if(Auth::user()->roles()->where('libelle', 'ADMINISTRATEUR')->exists() || Auth::user()->roles()->where('libelle', 'CONTROLEUR')->exists())
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Vérifier</button>
                                <!--  -->
                                <br> <br>
                                @endif
                                @csrf
                                <table id="example1" class="table table-bordered table-striped table-sm"
                                    style="font-size: 12px">
                                    <thead class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>Vérification</th>
                                            <th>Client</th>
                                            <th>Commentaire</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recouvrements as $recouvrement)
                                        <tr>
                                            <td>
                                                @if(Auth::user()->roles()->where('libelle', 'ADMINISTRATEUR')->exists() || Auth::user()->roles()->where('libelle', 'CONTROLEUR')->exists())
                                                <div class="form-check text-center">
                                                    <input @if($recouvrement->verified) disabled checked @endif class="form-check-input form-control" style="width: 20px;" type="checkbox" name="recouvrements[]" value="{{$recouvrement->id}}" id="checkIndeterminate">
                                                </div>
                                                @else
                                                ---
                                                @endif
                                            </td>
                                            <td class="ml-5 pr-5">{{ $recouvrement->client->raisonSociale }}</td>
                                            <td class="text-center">
                                                <textarea class="form-control" rows="1" id="" placeholder="{{$recouvrement->comments}}"></textarea>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($recouvrement->created_at)->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($recouvrement->verified)
                                                <span class="badge bg-success">Vérifié</span>
                                                @else
                                                <span class="badge bg-danger">Non Vérifié</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="text-white text-center bg-gradient-gray-dark">
                                        <tr>
                                            <th>N°</th>
                                            <th>Client</th>
                                            <th>Commentaire</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </form>
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
            "buttons": ["pdf", "print", "csv", "excel"],
            "order": [
                [0, 'asc']
            ],
            "pageLength": 15,

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