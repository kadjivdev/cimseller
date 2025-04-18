<?php

namespace App\Http\Controllers;

use App\Exports\ComptabiliteExport;
use App\Exports\ComptabiliteReExport;
use App\Mail\NotificateurProgrammationMail;
use App\Mail\NotificationAskUpdateVente;
use App\Mail\NotificationVenteMail;
use App\Models\EcheanceCredit;
use App\Models\User;
use App\tools\CommandeClientTools;
use Exception;
use App\Models\Zone;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Parametre;
use App\Models\TypeCommande;
use Illuminate\Http\Request;
use App\Models\CommandeClient;
use App\Models\DeletedVente;
use App\Models\filleuil;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Programmation;
use App\Models\UpdateVente;
use App\Models\Vendu;
use App\Models\VenteDeleteDemand;
use App\tools\ControlesTools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class VenteController extends Controller
{
    public $resultat;

    public function __construct()
    {
        $this->middleware('vendeur')->only(['create', 'store', 'update', 'delete']);
        ini_set("max_execution_time", 3600);
    }

    public function index(Request $request)
    {
        $roles = Auth::user()->roles()->pluck('id')->toArray();
        $commandeclients = CommandeClient::whereIn('statut', ['Préparation', 'Vendue', 'Validée', 'Livraison partielle', 'Livrée']);

        if ($request->debut && $request->fin) {
            $commandeclients = $commandeclients->WhereBetween('dateBon', [$request->debut, $request->fin])->pluck('id');
        } else {
            $commandeclients = $commandeclients->pluck('id');
        }

        $ventes = collect();
        Vente::whereIn('commande_client_id', $commandeclients)->orderByDesc('code')->chunk(100, function ($chunk) use (&$ventes, $request) {
            $ventes = $ventes->merge($chunk); //merge the chunk
        });

        if (in_array(1, $roles) || in_array(2, $roles) || in_array(5, $roles) || in_array(8, $roles) || in_array(9, $roles) || in_array(10, $roles) || in_array(11, $roles)) {
            $user = Auth::user();
            if ($user->id == 11) {
                $ventes = $ventes->where('statut', '<>', 'En attente de modification')->where("users", $user->id);
            } else {
                $ventes = $ventes->where('statut', '<>', 'En attente de modification');
            }
        } elseif (in_array(3, $roles)) {
            $ventes = $ventes->where('statut', '<>', 'Contrôller')
                ->where('statut', '<>', 'En attente de modification')
                ->where('users', Auth::user()->id);
        }

        return view('ventes.index', compact('ventes'));
    }

    public function indexCreate(Request $request)
    {
        $ventes = collect();
        if ($request->method() == 'GET') {
            $date = date('Y-m-d');
            Vente::whereDate('created_at', $date)->orderBy('created_at', 'DESC')->chunk(100, function ($chunk) use (&$ventes) {
                $ventes = $ventes->merge($chunk); //merge the chunk
            });
        } else {
            ##___POST METHOD
            $debut = $request->debut;
            $fin = $request->fin;
            Vente::whereBetween("created_at", [$debut, $fin])->orderBy('created_at', 'DESC')->chunk(100, function ($chunk) use (&$ventes) {
                $ventes = $ventes->merge($chunk); //merge the chunk
            });
        }
        // 
        return view('ventes.indexCreate', compact('ventes'));
    }

    public function indexControlle(Request $request)
    {
        $roles = Auth::user()->roles()->pluck('id')->toArray();

        $ventes = collect();

        if ($request->filtre) {
            ####___FILTRAGE
            Vente::whereBetween('ventes.created_at', [$request->debut, $request->fin])->where('statut', '<>', 'En attente de modification')->orderBy('date', 'DESC')->chunk(100, function ($chunk) use (&$ventes, $request) {
                $ventes = $ventes->merge($chunk); //merge the chunk
            });
        } else {
            # code...
            if (in_array(1, $roles) || in_array(2, $roles) || in_array(8, $roles) || in_array(9, $roles) || in_array(10, $roles) || in_array(11, $roles))
                Vente::where('statut', '=', 'Contrôller')->orderBy('date', 'ASC')->chunk(100, function ($chunk) use (&$ventes) {
                    $ventes = $ventes->merge($chunk); //merge the chunk
                });
            elseif (in_array(3, $roles))
                Vente::where('statut', '=', 'Contrôller')->where('users', Auth::user()->id)->orderByDesc('date')->chunk(100, function ($chunk) use (&$ventes) {
                    $ventes = $ventes->merge($chunk); //merge the chunk
                });
        }

        return view('ventes.indexControlle', compact('ventes'));
    }

    public function detailVente(Vente $vente)
    {
        $commandeclients = CommandeClient::whereIn('statut', ['Préparation', 'Vendue', 'Validée', 'Livraison partielle', 'Livrée'])->pluck('id');
        $ventes = Vente::whereIn('commande_client_id', $commandeclients)
            ->where('ventes.statut', '<>', 'Contrôller')
            ->where('ventes.statut', '<>', 'En attente de modification')
            ->where('ventes.id', $vente->id)
            ->join('vendus', 'vendus.vente_id', '=', 'ventes.id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('produits', 'produits.id', '=', 'ventes.produit_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('camions', 'camions.id', '=', 'programmations.camion_id')
            ->join('chauffeurs', 'chauffeurs.id', '=', 'programmations.chauffeur_id')
            ->select('ventes.code as vente', 'programmations.code', 'bon_commandes.code as codeBC', 'programmations.bl', 'camions.immatriculationTracteur', 'chauffeurs.nom', 'chauffeurs.prenom', 'vendus.qteVendu', 'ventes.destination', 'produits.libelle')
            ->orderByDesc('date')->get();
        return response()->json($ventes);
    }

    public function create(Request $request)
    {
        $typeVente = [];
        $user = User::find(Auth::user()->id);
        $repre = $user->representant;

        $zones = $repre->zones;

        //
        $clients = collect();

        if ($repre->nom == 'DIRECTION') {
            $zones = Zone::all();
            Client::chunk(100, function ($chunk) use (&$clients) {
                $clients = $clients->merge($chunk); //merge the chunk
            });
        } else {
            Client::where("zone_id", [$user->zone_id])->chunk(100, function ($chunk) use (&$clients) {
                $clients = $clients->merge($chunk); //merge the chunk
            });
        }

        if ($request->statuts) {
            if ($request->statuts == 1) {
                $typecommandeclient = TypeCommande::where('libelle', 'COMPTANT')->first();
                $commandeclients = CommandeClient::whereIn('statut', ['Non livrée', 'Livraison partielle'])->get();
                $req = $request->statuts;
            } elseif ($request->statuts == 2) {
                $typecommandeclient = TypeCommande::where('libelle', 'COMPTANT')->first();
                $commandeclients = CommandeClient::where('statut', 'Validée')->orWhere('statut', 'Livraison partielle')->whereNull('byvente')->where('type_commande_id', 2)->get();
                $req = $request->statuts;
            }
        } else {
            $typecommandeclient = TypeCommande::where('libelle', 'COMPTANT')->first();
            $commandeclients = CommandeClient::whereIn('statut', ['Non livrée', 'Livraison partielle'])->get();
            $req = 1;
        }

        $redirectto = $request->redirectto;
        $vente = NULL;

        return view('ventes.create', compact('vente', 'typecommandeclient', 'clients', 'commandeclients', 'zones', 'redirectto', 'req', 'typeVente'));
    }

    public function store(Request $request)
    {
        $req = NULL;
        if ($request->statuts == 1) {
            if ($request->type_vente_id == 1) {
                $validator = Validator::make($request->all(), [
                    'date' => ['required', 'before_or_equal:' . date('Y-m-d')],
                    'client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                    'transport' => ['required'],
                    'ctl_payeur' => ['required'],
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'date' => ['required', 'before_or_equal:' . date('Y-m-d')],
                    'client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                    'echeance' => ['required', 'after:' . date('Y-m-d')],
                    'transport' => ['required'],
                    'ctl_payeur' => ['required'],
                ]);
            }

            $req = $request->statut;
            if ($validator->fails()) {
                return redirect()->route('ventes.create', ['statuts' => $req])->withErrors($validator->errors())->withInput();
            }

            $format = env('FORMAT_COMMANDE_CLIENT');
            $parametre = Parametre::where('id', env('COMMANDE_CLIENT'))->first();
            $code = $format . str_pad($parametre->valeur, 7, "0", STR_PAD_LEFT);

            $commandeclients = CommandeClient::create([
                'code' => $code,
                'dateBon' => $request->date,
                'statut' => "Préparation",
                'type_commande_id' => $request->type_vente_id,
                'client_id' => $request->client_id,
                'zone_id' => $request->zone_id,
                'users' => Auth::user()->id,
                'byvente' => 1
            ]);

            if ($commandeclients) {

                $valeur = $parametre->valeur;

                $valeur = $valeur + 1;

                $parametres = Parametre::find(env('COMMANDE_CLIENT'));

                $parametre = $parametres->update([
                    'valeur' => $valeur,
                ]);

                if ($parametre) {
                    $format = env('FORMAT_VENTE_D');
                    $parametre = Parametre::where('id', env('VENTE'))->first();
                    $code = $format . str_pad($parametre->valeur, 7, "0", STR_PAD_LEFT);
                    if ($request->ctl_payeur == 0) {
                        $filleuls = json_encode([
                            'nomPrenom' => $request->nomPrenom,
                            'telephone' => $request->telephone,
                            'ifu' => $request->ifu
                        ]);
                    } else {
                        $filleuls = null;
                    }

                    $ventes = Vente::create([
                        'code' => $code,
                        'date' => $request->date,
                        'statut' => "Préparation",
                        'commande_client_id' => $commandeclients->id,
                        'users' => Auth::user()->id,
                        'type_vente_id' => $request->type_vente_id,
                        'transport' => $request->transport,
                        'ctl_payeur' => $request->ctl_payeur,
                        'filleuls' => $filleuls
                    ]);

                    if ($ventes) {

                        $valeur = $parametre->valeur;

                        $valeur = $valeur + 1;

                        $parametres = Parametre::find(env('VENTE'));

                        $parametres = $parametres->update([
                            'valeur' => $valeur,
                        ]);

                        if ($request->type_vente_id == 2) {
                            EcheanceCredit::create([
                                'date' => $request->echeance,
                                'statut' => 0,
                                'vente_id' => $ventes->id,
                                'user_id' => auth()->user()->id
                            ]);
                        }

                        if ($parametres) {
                            Session()->flash('message', 'Vente enregistrée avec succès!');
                            return redirect()->route('vendus.create', ['vente' => $ventes->id]);
                        }
                    }
                }
            }
        } elseif ($request->statuts == 2) {
            if ($request->type_vente_id == 1) {
                $validator = Validator::make($request->all(), [
                    'date' => ['required', 'before_or_equal:' . date('Y-m-d')],
                    'commande_client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'date' => ['required', 'before_or_equal:' . date('Y-m-d')],
                    'commande_client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                    'echeance' => ['required', 'after:' . date('Y-m-d')],
                    'transport' => ['required'],
                ]);
            }

            $req = $request->statuts;
            if ($validator->fails()) {
                return redirect()->route('ventes.create', ['statuts' => $req])->withErrors($validator->errors())->withInput();
            }

            $format = env('FORMAT_VENTE_C');
            $parametre = Parametre::where('id', env('VENTE'))->first();
            $code = $format . str_pad($parametre->valeur, 7, "0", STR_PAD_LEFT);

            $ventes = Vente::create([
                'code' => $code,
                'date' => $request->date,
                'statut' => "Préparation",
                'commande_client_id' => $request->commande_client_id,
                'users' => Auth::user()->id,
                'type_vente_id' => $request->type_vente_id,
                'transport' => $request->transport,
                'ctl_payeur' => $request->ctl_payeur
            ]);

            if ($ventes) {

                $valeur = $parametre->valeur;

                $valeur = $valeur + 1;

                $parametres = Parametre::find(env('VENTE'));

                $parametres = $parametres->update([
                    'valeur' => $valeur,
                ]);
                if ($request->type_vente_id == 2) {

                    EcheanceCredit::create([
                        'date' => $request->echeance,
                        'statut' => 0,
                        'vente_id' => $ventes->id,
                        'user_id' => auth()->user()->id
                    ]);
                }

                if ($parametres) {
                    Session()->flash('message', 'Vente enregistrée avec succès!');
                    return redirect()->route('vendus.create', ['vente' => $ventes->id]);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vente  $vente
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */

    public function show(Vente $vente)
    {
        return view('ventes.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vente  $vente
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory
     */

    public function edit(Request $request, Vente $vente)
    {
        $user = User::find(Auth::user()->id);
        $repre = $user->representant;
        $zones = $repre->zones;

        $clients = collect();
        Client::chunk(100, function ($chunk) use (&$clients) {
            $clients = $clients->merge($chunk); //merge the chunk
        });

        if ($repre->nom == 'DIRECTION') {
            $zones = Zone::all();
        }
        if ($vente->commandeclient->type_commande_id == 1) {
            $typecommandeclient = TypeCommande::where('libelle', 'COMPTANT')->first();
            $commandeclients = CommandeClient::whereIn('statut', ['Non livrée', 'Livraison partielle'])->get();
            $req = $vente->commandeclient->type_commande_id;
        } else {
            $typecommandeclient = TypeCommande::where('libelle', 'COMPTANT')->first();
            $commandeclients = CommandeClient::where('statut', 'Non livrée')->orWhere('statut', 'Livraison partielle')->where('type_commande_id', 2)->get();
            $req = $vente->commandeclient->type_commande_id;
        }

        $redirectto = $request->redirectto;
        return view('ventes.edit', compact('vente', 'typecommandeclient', 'clients', 'commandeclients', 'zones', 'redirectto', 'req'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vente  $vente
     * @return \Illuminate\Http\RedirectResponse
     */

    public function update(Request $request, Vente $vente)
    {
        try {
            $req = NULL;
            if ($vente->commandeclient->type_commande_id == 1) {

                $validator = Validator::make($request->all(), [
                    'date' => ['required', 'before_or_equal:' . date('Y-m-d')],
                    'client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                    'transport' => ['required'],
                ]);

                $req = $vente->commandeclient->type_commande_id;
                if ($validator->fails()) {
                    return redirect()->route('ventes.edit', ['vente' => $vente->id])->withErrors($validator->errors())->withInput();
                }

                $format = env('FORMAT_VENTE_D');
                $parametre = Parametre::where('id', env('VENTE'))->first();
                $code = $format . str_pad($parametre->valeur, 4, "0", STR_PAD_LEFT);

                $vente->commandeclient()->update([
                    'type_commande_id' => $request->type_vente_id,
                    'client_id' => $request->client_id,
                    'zone_id' => $request->zone_id
                ]);
                $vente->update([
                    'date' => $request->date,
                ]);

                Session()->flash('message', 'Vous avez modifier avec succès la vente. Faite la relecture du détails');
                return redirect()->route('vendus.create', ['vente' => $vente->id]);
            } elseif ($vente->commandeclient->type_commande_id == 2) {

                $validator = Validator::make($request->all(), [
                    'date' => ['required'],
                    'commande_client_id' => ['required'],
                    'zone_id' => ['required'],
                    'type_vente_id' => ['required'],
                    'transport' => ['required'],
                ]);
                $req = $vente->commandeclient->type_commande_id;
                if ($validator->fails()) {
                    return redirect()->route('ventes.create', $req)->withErrors($validator->errors())->withInput();
                }

                $vente->update([
                    'date' => $request->date,
                    'commande_client_id' => $request->commande_client_id,
                    'users' => Auth::user()->id,
                    'type_vente_id' => $request->type_vente_id,
                    'transport' => $request->transport,
                    'ctl_payeur' => $request->ctl_payeur
                ]);

                if ($request->type_vente_id == 1) {
                    $vente->echeances()->delete();
                }

                Session()->flash('message', 'Vous avez modifier avec succès la vente. Faite la relecture du détails');
                return redirect()->route('vendus.create', ['vente' => $vente->id]);
            }
        } catch (Exception $e) {
            if (env('APP_DEBUG') == TRUE) {
                return $e;
            } else {

                Session()->flash('error', 'Opps! Enregistrement échoué. Veuillez contacter l\'administrateur système!');
                return redirect()->route('vendus.index');
            }
        }
    }

    public function delete(Vente $vente)
    {
        if (Auth::user()->id == $vente->user->id) {
            return view('ventes.delete', compact('vente'));
        } else {
            Session()->flash('error', 'Vos n\'êtes eligible à une suppression. Cette vente n evous appartient pas!');
            return redirect()->route('ventes.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vente  $vente
     * @return \Illuminate\Http\RedirectResponse
     */

    public function destroy(Vente $vente)
    {
        if (!$vente) {
            return redirect()->back()->with("error", "Cette vente n'existe plus!");
        };

        ###___ENREGISTREMENT DE LA VENTE A SUPPRIMER 
        $data = [
            "code" => $vente->code,
            "date" => $vente->date,
            "montant" => $vente->montant,
            "statut" => $vente->statut,
            "commande_client_id" => $vente->commande_client_id,
            "users" => $vente->users,
            "pu" => $vente->pu,
            "qteTotal" => $vente->qteTotal,
            "remise" => $vente->remise,
            "produit_id" => $vente->produit_id,
            "type_vente_id" => $vente->type_vente_id,
            "vente_validation" => $vente->vente_validation,
            "transport" => $vente->transport,
            "destination" => $vente->destination,
            "ctl_payeur" => $vente->commandeclient->client->id,
            "date_comptabilisation" => $vente->date_comptabilisation,

            "date_traitement" => $vente->date_traitement,
            "user_traitement" => $vente->user_traitement,
            "date_envoie_commercial" => $vente->date_envoie_commercial,
            "user_envoie_commercial" => $vente->user_envoie_commercial,
            "comptabiliser" => $vente->comptabiliser,
            "comptabiliser_history" => $vente->comptabiliser_history,
            "annuler" => $vente->annuler,
            "valide" => $vente->valide,
            "validated_date" => $vente->validated_date,
            "traited_date" => $vente->traited_date,
            "restitutor" => auth()->user()->id,
            // "restituted" => true,
            "reglement" => $vente->reglements->sum("montant"),
        ];

        $venteDeleted = DeletedVente::create($data);

        ###___
        ControlesTools::generateLog($vente, 'Vente', 'Suppression ligne');
        if ($vente->vendus) {
            $vente->vendus()->delete();
            $vente->commandeclient()->delete();

            // ####____ SUPPRESSION DES REGLEMENTS LIES A CETTE VENTE
            foreach ($vente->reglements as $regle) {
                ###___
                $mvts = $regle->_mouvements;

                foreach ($mvts as $mvt) {
                    ####____Suppression du Mouvement
                    $mvt->delete();

                    ####___DESPORMAINS LA RESTITUTION SE FAIT SEULEMENT QUAND IL Y A DE REGLEMENT SUR LA VENTE
                    $venteDeleted->restituted = true;
                    $venteDeleted->save();
                }

                ####____SUPPRESSION DU REGLEMENT
                $regle->delete();
            }
            // ####___

            $vente->delete();
        } else {
            $vente->commandeclient()->delete();
            $vente->delete();
        }

        ####====______
        foreach ($vente->vendus as $vendu) {
            $programmation = $vendu->programmation;
            $bl = $programmation->bl;
            $bl_gest = $programmation->bl_gest;

            if ($bl_gest != $bl) {
                $programmation->bl = null;
                $programmation->bl_gest = null;
                $programmation->statut = 'Valider';
                $programmation->save();
            }
        }

        ####____ON BLOQUE A NOUVEAU L'ACCES
        $venteDeleteDemande =  VenteDeleteDemand::where(["vente" => $vente->id, "demandeur" => auth()->user()->id, "deleted" => false])->first();
        if ($venteDeleteDemande) {
            $venteDeleteDemande->valide = false;
            $venteDeleteDemande->deleted = true;
            $venteDeleteDemande->save();
        }

        #####___
        Session()->flash('message', 'Vente supprimée avec succès.');
        return redirect()->route('ventes.index', ['message' => $vente]);
    }

    public function invalider(Vente $vente)
    {
        return view('ventes.invalider', compact('vente'));
    }

    public function posteInvalider(Vente $vente)
    {
        $vente->update(['statut' => 'Préparation']);
        CommandeClientTools::changeStatutCommande($vente->commandeclient);
        return redirect()->route('ventes.index')->with('message', 'Votre vente est passé en préparation.');
    }

    public function validationVente(Vente $vente)
    {
        if ($vente->statut == "Vendue") {
            Session()->flash('message', 'Vous avez déjà valider cette vente n° ' . $vente->code);
            return redirect()->route('ventes.index', ['vente' => $vente->id]);
        }
        if ($vente->vendus()->sum('qteVendu') && $vente->vendus()->sum('qteVendu') == $vente->qteTotal) {
            $vente->update(['statut' => 'Vendue', "validated_date" => now()]);

            CommandeClientTools::changeStatutCommande($vente->commandeclient);
            $venteAttentes = DB::select("
                SELECT date,COUNT(*) AS nombre
                FROM ventes
                WHERE statut = 'Vendue'
                GROUP BY date
            ");
            $desMail = User::find(env('GESTIONNAIRE_VENTE'));
            $copieMail = User::find(env('COPIE_GESTIONNAIRE_VENTE'));
            $message = "<p>Nous vous notifions une nouvelle vente effectuée par <b>" . $vente->user->name . "</b>. Merci de vous connecter pour traiter.</p>";
            $mail = new NotificationVenteMail(['email' => $desMail->email, 'nom' => $desMail->nom], 'Vente n° ' . $vente->code . ' du ' . date_format(date_create($vente->date), 'd/m/Y'), $message, $vente, $venteAttentes, $copieMail->email);
            Mail::send($mail);
            return redirect()->route('ventes.index')->with('message', 'Félicitation! Votre vente a été enregistrée');
        } else
            abort(403);
    }

    public function initVente(Vente $vente)
    {
        $vente->update([
            'statut' => 'Préparation',
            'montant' => null,
            'qteTotal' => null,
            'pu' => null,
            'produit_id' => null,
            'remise' => null,
            'destination' => null
        ]);
        $vente->vendus()->delete();
        return redirect()->route('vendus.create', ['vente' => $vente->id])->with('msgSuppression', 'Vente initialisée.');
    }

    public function aComptabiliser(Vente $vente)
    {
        try {
            $vente->date_envoie_commercial = date('Y-m-d');
            $vente->user_envoie_commercial = Auth()->user()->id;
            $vente->update();
            return redirect()->back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function cltpayeur(Client $client)
    {
        $filleuls = $client->filleulFisc;
        $clientParrain = [];
        foreach ($filleuls as $filleul) {
            $clt = Client::find($filleul);
            if ($clt)
                $clientParrain[] = $clt;
        }
        return response($clientParrain);
    }

    public function showVente(Vente $vente)
    {
        return response()->json($vente);
    }

    public function demandeVente(Request $request)
    {
        try {
            $vente = Vente::find($request->id);

            $objetDemande =  json_encode([
                'dateDemande' => date("Y-m-d H:i:s"),
                'user_id' => Auth()->user()->id,

                'qteOld' => $vente->qteOld,
                'prixUnitaireOld' => $vente->pu,
                'transportOld' => $vente->transport,

                'observation' => $request->observation,

                'qteNew' => $request->qteNew,
                'prixUnitaireNew' => $request->PrixUnitaireNew,
                'transportNew' => $request->PrixTransportNew,
            ]);

            if ($vente->ask_history) {
                $ask_history = json_decode($vente->ask_history);
                $ask_history[] = $objetDemande;
                $ask_history = json_encode($ask_history);
                $vente->update(['ask_history' => $ask_history, 'statut' => 'En attente de modification']);
            } else {
                $ask_history = [$objetDemande];
                $ask_history = json_encode($ask_history);
                $vente->update(['ask_history' => $ask_history, 'statut' => 'En attente de modification']);
            }

            $desMail = User::find(env('GESTIONNAIRE_ID'));
            $copieMail = User::find(env('COPIE_GESTIONNAIRE_VENTE'));
            $message = "
                <p>Nous vous notifions une nouvelle demande de modification vente effectuée par <b>" . Auth()->user()->name . "</b> <br>.
                <b>Ci-joint l'observation de modification<b>:<br> <i>" . $request->observation . "</i>
                </p>";
            $lienAction =  route('ventes.askUpdate');
            $mail = new NotificationAskUpdateVente(['email' => $desMail->email, 'nom' => $desMail->nom], 'Demande de mofication Vente n° ' . $vente->code . ' du ' . date_format(date_create($vente->date), 'd/m/Y'), $message, $vente, $copieMail->email, $lienAction);
            Mail::send($mail);
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function askUpdate()
    {
        $ventes = Vente::where('statut', 'En attente de modification')->orderByDesc('date')->get();
        return view('ventes.askUpadate', compact('ventes'));
    }

    public function envoieComptabilite(Request $request)
    {
        $ventes = explode(",", $request->ventes);

        ####____
        if (!$ventes[0]) {
            return redirect()->back()->with("error", "Aucune vente n'a été selectionnée!");
        }

        foreach ($ventes as  $vente) {

            $vente = Vente::find($vente);
            $vente->date_envoie_commercial = date('Y-m-d');
            $vente->user_envoie_commercial = Auth()->user()->id;
            // ON CONFIRME QUE CETTE VENTE EST VALIDE
            $vente->valide = true;
            $vente->update();
        }

        return redirect()->back()->with("message", "Envoie à la comptabilité effectuée avec succès!");
    }

    public function venteAEnvoyerComptabiliser()
    {
        ####____
        $current = Auth::user();
        $user_representant = $current->representant; ###___il est representant ou il est sous un representant

        if (IS_HIPLYTE_ACCOUNT($current)) {
            $user_representant = $current;
        }

        ##____
        $AEnvoyers = collect();
        Vente::orderBy('id', 'desc')->whereIn('statut', ['Vendue', 'Contrôller', 'Soldé'])->where('date_envoie_commercial', NULL)->where("users", "!=", $current->id)->chunk(100, function ($chunk) use (&$AEnvoyers) {
            $AEnvoyers = $AEnvoyers->merge($chunk); //merge the chunk
        });
        return view('comptabilite.listesVenteAEnvoyer', compact('AEnvoyers'));
    }

    ####___TOUTES VENTES CONFONDUES (TRAITEES OU PAS)
    public function getVenteAComptabiliser()
    {
        return view('comptabilite.listesVente');
    }

    public function postVenteAComptabiliser(Request $request)
    {
        $AComptabilisers = collect();
        Vente::where('date_envoie_commercial', '<>', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])
            ->chunk(100, function ($chunk) use (&$AComptabilisers) {
                $AComptabilisers = $AComptabilisers->merge($chunk); //merge the chunk
            });

        $AComptabilisers =  $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', '<>', 4)
            ->orderBy('date', 'DESC')
            ->get();

        $AComptabilisersAdjeOla = $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', 4)
            ->orderBy('date', 'DESC')
            ->get();

        session(['debut_compta' => $request->debut]);
        session(['fin_compta' => $request->fin]);

        return redirect()->route('ventes.venteAComptabiliser')->withInput()->with('resultat', ['AComptabilisers' => $AComptabilisers, 'AComptabilisersAdjeOla' => $AComptabilisersAdjeOla, 'debut' => $request->debut, 'fin' => $request->fin]);
    }

    ####___TOUTES VENTES A TRAITER
    public function getVenteATraiter(Request $request)
    {

        $AComptabilisers =  Vente::where('date_envoie_commercial', '<>', NULL)
            ->where('date_traitement', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', '<>', 4)
            ->orderBy('date', 'DESC')
            ->get();

        $AComptabilisersAdjeOla = Vente::where('date_envoie_commercial', '<>', NULL)
            ->where('date_traitement', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', 4)
            ->orderBy('date', 'DESC')
            ->get();

        if ($request->debut && $request->fin) {
            session(["search" => true]);
            session(["debut" => $request->debut]);
            session(["fin" => $request->fin]);
            $AComptabilisers = $AComptabilisers->whereBetween("validated_date", [$request->debut, $request->fin]);
            $AComptabilisersAdjeOla = $AComptabilisersAdjeOla->whereBetween("validated_date", [$request->debut, $request->fin]);
        }

        return view('comptabilite.atraiter', compact("AComptabilisers", "AComptabilisersAdjeOla"));
    }

    public function postVenteATraiter(Request $request)
    {
        $AComptabilisers = collect();
        Vente::where('date_envoie_commercial', '<>', NULL)
            ->where('date_traitement', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])
            ->chunk(100, function ($chunk) use (&$AComptabilisers) {
                $AComptabilisers = $AComptabilisers->merge($chunk); //merge the chunk
            });

        $AComptabilisers =  $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', '<>', 4)
            ->orderBy('date', 'DESC')
            ->get();


        $AComptabilisersAdjeOla = $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', 4)
            ->orderBy('date', 'DESC')
            ->get();

        session(['debut_compta' => $request->debut]);
        session(['fin_compta' => $request->fin]);

        return redirect()->route('ventes.getVenteAtraiter')->withInput()->with('resultat', ['AComptabilisers' => $AComptabilisers, 'AComptabilisersAdjeOla' => $AComptabilisersAdjeOla, 'debut' => $request->debut, 'fin' => $request->fin]);
    }

    #####____ LES TOTAUX
    public function getVenteTotaux()
    {
        return view('comptabilite.totaux');
    }

    public function postVenteTotaux(Request $request)
    {
        $AComptabilisers = collect();
        Vente::where('date_envoie_commercial', '<>', NULL)
            ->where('date_traitement', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])
            ->chunk(100, function ($chunk) use (&$AComptabilisers) {
                $AComptabilisers = $AComptabilisers->merge($chunk); //merge the chunk
            });

        $AComptabilisers = $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', '<>', 4)
            ->orderBy('date', 'DESC')
            ->get();

        $AComptabilisersAdjeOla = $AComptabilisers

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', 4)
            ->orderBy('date', 'DESC')
            ->get();

        session(['debut_compta' => $request->debut]);
        session(['fin_compta' => $request->fin]);

        return redirect()->route('ventes.getVenteTotaux')->withInput()->with('resultat', ['AComptabilisers' => $AComptabilisers, 'AComptabilisersAdjeOla' => $AComptabilisersAdjeOla, 'debut' => $request->debut, 'fin' => $request->fin]);
    }

    #####____GET DES VENTES SUPPRIMEES
    public function getVenteAComptabiliserDeleted()
    {
        return view('comptabilite.listesVenteDeleted');
    }

    #####____POST DES VENTES SUPPRIMEES
    public function postVenteAComptabiliserDeleted(Request $request)
    {
        $AComptabilisers = DeletedVente::where("date_envoie_commercial", "!=", null)->orderBy('date', 'DESC')
            ->whereDate('created_at', '>=', $request->debut)
            ->whereDate('created_at', '<=', $request->fin)
            ->get();

        session(['debut_compta' => $request->debut]);
        session(['fin_compta' => $request->fin]);

        return redirect()->route('ventes.venteAComptabiliserDeleted')->withInput()->with('resultat', ['AComptabilisers' => $AComptabilisers, 'debut' => $request->debut, 'fin' => $request->fin]);
    }


    #####____GET DES VENTES MODIFEES
    public function getVenteAComptabiliserUpdated()
    {
        return view('comptabilite.listesVenteUpdated');
    }

    #####____POST DES VENTES MODIFIEES
    public function postVenteAComptabiliserUpdated(Request $request)
    {
        $AComptabilisers =  Vente::where('date_envoie_commercial', '<>', NULL)
            ->where('date_traitement', NULL)->whereIn('ventes.statut', ['Vendue', 'Contrôller', 'Soldé'])

            ###__on recherche desormais via la date de validation
            ->whereDate('ventes.validated_date', '>=', $request->debut)
            ->whereDate('ventes.validated_date', '<=', $request->fin)

            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'fournisseurs.sigle')->where('fournisseurs.id', '<>', 4)
            ->orderBy('date', 'DESC')
            ->get();


        session(['debut_compta' => $request->debut]);
        session(['fin_compta' => $request->fin]);

        return redirect()->route('ventes.venteAComptabiliserUpdated')->withInput()->with('resultat', ['AComptabilisers' => $AComptabilisers, 'debut' => $request->debut, 'fin' => $request->fin]);
    }

    public function ventATraiter(Vente $vente)
    {
        $payeur = $vente->filleule;
        $client = $vente->commandeclient->client;
        return view('comptabilite.traitementVente', compact('vente', 'client', 'payeur'));
    }

    public function traiterVente(Request $request, Vente $vente)
    {
        try {
            $vente->taux_aib = $request->taux_aib;
            $vente->taux_tva = $request->taux_tva;
            $vente->prix_Usine = $request->prix_Usine;
            $vente->prix_TTC = $request->prix_TTC;
            $vente->marge = $request->marge;
            $vente->date_traitement = date('Y-m-d');
            $vente->user_traitement = Auth()->user()->id;
            $vente->traited_date = now();
            $vente->update();
            return redirect()->route("ventes.getVenteAtraiter")->with("message", "Vente traitée avec succès!");
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function listeDesTraitementPeriode()
    {
        return view('comptabilite.listesDesTraitementPeriode');
    }

    public function postListeDesTraitementPeriode(Request $request)
    {
        //Prévoir le validator
        $request->validate([
            'debut' => ['required'],
            'fin' => ['required']
        ]);

        $ventes = Vente::where('ventes.statut', 'Contrôller')
            ->whereBetween('date_traitement', [$request->debut, $request->fin])->orderByDesc('ventes.code')
            ->join('commande_clients', 'ventes.commande_client_id', '=', 'commande_clients.id')
            ->join('clients', 'commande_clients.client_id', '=', 'clients.id')
            ->join('vendus', 'ventes.id', '=', 'vendus.vente_id')
            ->join('programmations', 'programmations.id', '=', 'vendus.programmation_id')
            ->join('detail_bon_commandes', 'detail_bon_commandes.id', '=', 'programmations.detail_bon_commande_id')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'detail_bon_commandes.bon_commande_id')
            ->join('fournisseurs', 'fournisseurs.id', '=', 'bon_commandes.fournisseur_id')
            ->select('ventes.*', 'clients.*', 'bon_commandes.code as codeBon', 'bon_commandes.dateBon', 'fournisseurs.sigle as fournisseur')
            ->with('produit', 'payeur')
            ->get();

        return redirect()->route('ventes.listeDesTraitementPeriode')->withInput()->with('resultat', ['ventes' => $ventes, 'debut' => $request->debut, 'fin' => $request->fin]);
    }

    public function filleuilfictive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'ifu' => ['required'],
        ]);
        if ($validator->fails()) {
            Session()->flash('message', 'Renseigner les champ s\'il vous plait.');
            return back();
        }
        $filleul = new filleuil;
        $filleul->name = $request->name;
        $filleul->ifu = $request->ifu;
        $filleul->save();
        return back();
    }

    public function testMail()
    {
        $mail = new NotificateurProgrammationMail('test', ['email' => 'to@exemple.com', 'nom' => 'KANHONOU Arnauld'], 'Nouvelle commande', 'Bonjour');
        Mail::send($mail);
    }

    public function viewVenteTraiter()
    {
        $fournisseurs = Fournisseur::all();
        return view('comptabilite.etatcomptabilite', compact('fournisseurs'));
    }

    public function viewVenteComptabiliser()
    {
        $fournisseurs = Fournisseur::all();
        return view('comptabilite.etatDejacomptabilite', compact('fournisseurs'));
    }

    public function postexport(Request $request)
    {
        $comptabiliser = [];
        if ($request->fournisseur == "tous") {
            $comptabiliser =  DB::select(
                "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_traitement` BETWEEN ? AND ?
                            
                            AND  `export_comptabilite`.`date_comptabilisation` IS NULL
                            ORDER BY `export_comptabilite`.`date_traitement` DESC;
                 ",
                [$request->debut, $request->fin]
            );
        } else {
            $comptabiliser =  DB::select(
                "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_traitement` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`FRS` = ?
                            AND  `export_comptabilite`.`date_comptabilisation` IS NULL
                            ORDER BY `export_comptabilite`.`date_traitement` DESC;
                 ",
                [$request->debut, $request->fin, $request->fournisseur]
            );
        }

        foreach ($comptabiliser as $key => $comptabilise) {
            if ($comptabilise->filleuls !== null) {
                $compta = json_decode($comptabilise->filleuls);
                $comptabiliser[$key]->clientFilleuls = $compta->nomPrenom;
                $comptabiliser[$key]->clientFilleulsifu = $compta->ifu;
                unset($comptabiliser[$key]->filleuls);
            } else {
                $comptabiliser[$key]->clientFilleuls = '';
                $comptabiliser[$key]->clientFilleulsifu = '';
                unset($comptabiliser[$key]->filleuls);
            }
        }

        return redirect()->route('ventes.viewVenteTraiter')->withInput()->with('resultat', ['comptabilisers' => $comptabiliser, 'debut' => $request->debut, 'fin' => $request->fin, 'filtre' => $request->filtre]);
    }

    public function postDejaExport(Request $request)
    {
        $comptabiliser = [];
        if ($request->filtre == 'traitement') {
            session(['filtre' => $request->filtre]);
            if ($request->fournisseur) {
                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_traitement` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`date_comptabilisation` IS NOT NULL
                            AND  `export_comptabilite`.`FRS` =?
                            ORDER BY `export_comptabilite`.`date_traitement`DESC;
                    ",
                    [$request->debut, $request->fin, $request->fournisseur]
                );
            } else {

                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_comptabilisation` BETWEEN ? AND ?
                            ORDER BY `export_comptabilite`.`date_traitement`DESC;

                ",
                    [$request->debut, $request->fin]
                );
            }
        } elseif ($request->filtre == 'comptabilisation') {
            session(['filtre' => $request->filtre]);
            if ($request->fournisseur) {
                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_comptabilisation` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`date_traitement` IS NOT NULL
                            AND  `export_comptabilite`.`FRS` = ?
                            ORDER BY `export_comptabilite`.`date_comptabilisation`DESC;
                    ",
                    [$request->debut, $request->fin, $request->fournisseur]
                );
            } else {

                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_comptabilisation` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`date_traitement` IS NOT NULL
                            ORDER BY `export_comptabilite`.`date_traitement`DESC;
                            
                ",
                    [$request->debut, $request->fin]
                );
            }
        } else {
            session(['filtre' => $request->filtre]);
            if ($request->fournisseur) {
                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`date_traitement` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`date_comptabilisation` IS NOT NULL
                            AND  `export_comptabilite`.`FRS` = ?
                            ORDER BY `export_comptabilite`.`date_traitement`DESC;
                    ",
                    [$request->debut, $request->fin, $request->fournisseur]
                );
            } else {

                $comptabiliser =  DB::select(
                    "SELECT  *           
                        FROM `export_comptabilite`  
                            WHERE  `export_comptabilite`.`dateCreate` BETWEEN ? AND ?
                            AND  `export_comptabilite`.`date_traitement` IS NOT NULL
                            AND  `export_comptabilite`.`date_comptabilisation` IS NOT NULL
                            ORDER BY `export_comptabilite`.`date_traitement`DESC;
                            
                ",
                    [$request->debut, $request->fin]
                );
            }
        }

        foreach ($comptabiliser as $key => $comptabilise) {
            if ($comptabilise->filleuls !== null) {
                $compta = json_decode($comptabilise->filleuls);
                $comptabiliser[$key]->clientFilleuls = $compta->nomPrenom;
                $comptabiliser[$key]->clientFilleulsifu = $compta->ifu;
                unset($comptabiliser[$key]->filleuls);
            } else {
                $comptabiliser[$key]->clientFilleuls = '';
                $comptabiliser[$key]->clientFilleulsifu = '';
                unset($comptabiliser[$key]->filleuls);
            }
        }

        return redirect()->route('ventes.viewVenteComptabiliser')->withInput()->with('resultat', ['comptabilisers' => $comptabiliser, 'debut' => $request->debut, 'fin' => $request->fin, 'filtre' => $request->filtre]);
    }

    public function export($debut, $fin, $filtre)
    {
        $fileName = 'comptabiliser';
        $date = date('Ymd');

        return Excel::download(new ComptabiliteExport($debut, $fin, $filtre), $fileName . '_' . $date . '.xlsx');
    }

    public function ReExport($debut, $fin, $filtre)
    {
        $fileName = 'ReExport_comptabiliser';
        $date = date('Ymd');

        return Excel::download(new ComptabiliteReExport($debut, $fin, $filtre), $fileName . '_' . $date . '.xlsx');
    }

    #######__________UPDATE VENTE ________#################
    function askUpdateVente(Request $request, Vente $vente)
    {
        ###___ON NE PEUT QUE MODIFIER LA VENTE QU'ON A PASSEE
        if (Auth::user()->id != $vente->user->id) {
            return redirect()->back()->with("error", "Cette vente ne vous appartient pas! Vous ne pouvez pas éffectuer cette opération");
        };

        if ($request->method() == "GET") {
            ####______VOYONS SI CETTE DEMANDE A DEJA ETE FAITE PAR CE USER
            $isThisDemandOnceMadeByThisUser = false;
            $isThisDemandValidatedForThisUser = false;
            foreach ($vente->_updateDemandes as $demand) {
                if ($demand->demandeur == auth()->user()->id) {
                    $isThisDemandOnceMadeByThisUser = true;

                    ###___SI LA DEMANDE EST VALIDEE
                    if ($demand->valide) {
                        $isThisDemandValidatedForThisUser = true;
                    }
                }
            }

            # quand la demande a déjà été faite
            if (IsThisVenteUpdateDemandeOnceMade($vente)) {
                $products = Produit::all();
                $clients = Client::all();

                ####____SI LA DEMANDE MODIFIEE N'EST PAS ACTUELLEMENT VALIDEE
                if (!IsThisVenteUpdateDemandeAlreadyValidated($vente)) {
                    #####________ECRIRE A NOUVEAU UNE DEMANDE DE MODIFICATION
                    return view("ventes.askUpdateVente", compact('vente'));
                }

                #####____SI C'EST VALIDEE ON PASSE A LA MODIFICATION VRAIE
                return view("ventes.updateVente", compact("vente", "clients", "products")); ##  redirect()->back()->with("error", "Vous avez déjà éffectuée cette requête! Vous ne pouvez la refaire à nouveau");

            } else {

                #####________Faire une demande
                return view("ventes.askUpdateVente", compact('vente'));
            }
        }

        if ($request->method() == "POST") {

            if (!$vente) {
                return redirect()->back()->with("error", "La vente est réquise pour cette requête!");
            }


            Validator::make(
                $request->all(),
                [
                    "raison" => ["required"],
                    "prouve_file" => ["required"],
                ],
                [
                    "raison.required" => "Ce champ est réquis!",
                    "prouve_file.required" => "Ce champ est réquis!",
                ]
            )->validate();

            ###___TRAITEMENT DE L'IMAGE
            if ($request->hasFile("prouve_file")) {
                # code...
                $img = $request->file("prouve_file");
                $img_name = $img->getClientOriginalName();
                $img->move("files", $img_name);

                $prouve_file = asset("/files/" . $img_name);
            }
            ###___

            $data = array_merge($request->all(), [
                "vente" => $vente->id,
                "demandeur" => auth()->user()->id,
                "prouve_file" => $prouve_file,
            ]);

            UpdateVente::create($data);

            ###___
            return redirect('/ventes/index')->with("message", "Demande de modification de vente effectuée avec succès! Patientez en attendant que la réquête soit traitée et validée!");
        }
    }

    ####____UPDATE VENTE TRULLY
    ####____ ON MODIFIE LA VENTE SEULEMENT POUR UNE PROGRAMMATION DONNEE
    ####_____(il faut donc preciser la programmation concernée)
    function _updateVente(Request $request)
    {
        $vente = Vente::findOrFail($request->vente);

        ###___ON NE PEUT QUE MODIFIER LA VENTE QU'ON A PASSEE
        if (Auth::user()->id != $vente->user->id) {
            return redirect()->back()->with("error", "Cette vente ne vous appartient pas! Vous ne pouvez pas éffectuer cette opération");
        };

        ####____QUAND C'EST NI MODIFIE NI VALIDEE
        if (!IsThisVenteUpdateDemandeAlreadyValidated($vente) && !IsThisVenteUpdateDemandeAlreadyModified($vente)) {
            return redirect("/ventes/index")->with("error", "Désolé! Vous n'avez plus accès à cette modification! Veillez écrire à nouveau une demande de modification");
        }

        ####___faire une validation ici avant de continuer

        $request->validate([
            "pu" => ["numeric"],
            "qteVendu" => ["numeric"],
            "produit" => ["numeric"],
            "transport" => ["numeric"],
            "montant" => ["numeric"],
        ]);

        ####____REFORMATTAGE DES DATAS
        $pu = $request->pu; ## $vente->pu;
        $qteVendu = $request->qteVendu;
        // $transport = $request->transport;

        // $venteMontant = $pu * $qteVendu;
        $produit_id = $request->produit ? $request->produit : $request->produit_id;
        $client = $request->client_id ? $request->client_id : $vente->client_id;
        $programmation_id = $request->programmation_id;

        ###___MODIFICATION DU VENDU
        $vp_vendu = Vendu::where(["vente_id" => $vente->id, "programmation_id" => $programmation_id])->first();
        if ($vp_vendu) {
            ### i.e la programmation n'a pas subie de modification
            #### la vente est toujours associée à cette programmation
            $vendu = $vp_vendu;
        } else {
            ### i.e la programmation a pas subie de modification
            #### la vente n'est plus associée à cette programmation
            $vendu =  $vente->vendus->first();
        }

        ####____
        $programmation = Programmation::findOrFail($programmation_id);
        $pr_totalVendus = $programmation->vendus->sum("qteVendu"); ###Total vendu sur cette programmation

        ###___Stock actuel du camion
        $current_stock = $programmation->qteprogrammer - ($pr_totalVendus - $qteVendu);

        if ($qteVendu > $current_stock) {
            return redirect()->back()->with("error", "La quantité entrée est supérieure au stock du camion choisi. Veuillez bien diminuer la quantité");
        }

        ###__Ce que le stock du camion deviendra si on ajoute le nouveau *qteVendu*  
        $vd_vendu = $vendu->qteVendu; ###Qte precedemment vendue sur cette vente liée à cette programmation
        $qteTotalProgrammerCamion = $vendu->programmation->qteprogrammer; ###Qte totale programmée vendue sur cette camion 
        $stock = $qteTotalProgrammerCamion - (($pr_totalVendus - $vd_vendu) + $qteVendu);

        if ($stock < 0) {
            return redirect()->back()->with("error", "Le Stock de ce camion sera : " . $stock . " si vous rentrez une telle quantité. Veuillez bien diminuer la quantité");
        }

        if ($vendu) {
            $vendu->update([
                "qteVendu" => $qteVendu,
                "pu" => $pu,
                "programmation_id" => $programmation_id,
            ]);
        }

        ###_____MODIFICATION DE LA VENTE EN REEL
        $vente->update([
            "qteTotal" => $qteVendu,
            "montant" => $request->montant,
            "produit_id" => $produit_id,
            "pu" => $pu,
            "transport" => $request->transport,
        ]);

        ###___MODIFICATION DE LA COMMANDE CLIENT (si la modification touche le client)
        $vente->commandeclient->update(["client_id" => $client, "montant" => $request->montant]);

        ###___MODIFICATION DU MONTANT DE REGLEMENT DE LA VENTE
        if ($vente->reglements->first()) {
            $vente->reglements->first()->update(["montant" => $request->montant]);
        }

        ####____ON BLOQUE A NOUVEAU L'ACCES
        $venteUpdateDemande = UpdateVente::where(["vente" => $vente->id, "demandeur" => auth()->user()->id])->get()->last();
        $venteUpdateDemande->valide = false;
        $venteUpdateDemande->modified = true;
        $venteUpdateDemande->save();

        ###___
        return redirect("/ventes/index")->with("message", "Vente modifiée avec succès!");
    }

    #####________VENTES VALIDATIONS
    public function Validation(Request $request)
    {
        if ($request->method() == "POST") {
            $demand = UpdateVente::find($request->demand);
            if (!$demand) {
                return redirect()->back()->with("error", "Cette demande de modification n'existe pas!");
            }

            ###___MODIFICATION
            $demand->valide = 1;
            $demand->save();

            return redirect()->back()->with("message", "Demande validée avec succès!");
        }

        ####___
        $venteUpdateDemands = UpdateVente::OrderBy("valide", "asc")->get();

        return view("ventes.venteUpdateDemand", compact("venteUpdateDemands"));
    }

    public function DeleteDemandVenteUpdate(Request $request, UpdateVente $demand)
    {
        if (!$demand) {
            return back()->with("error", "Cette demande de modification n'existe plus!");
        }

        $demand->delete();
        return back()->with("message", "Demande annulée avec succès!");
    }
    #######__________END UPDATE VENTE ________#################


    #######__________DELETE VENTE ________#################
    function askDeleteVente(Request $request, Vente $vente)
    {
        ###___ON NE PEUT QUE MODIFIER LA VENTE QU'ON A PASSEE
        if (Auth::user()->id != $vente->user->id) {
            return redirect()->back()->with("error", "Cette vente ne vous appartient pas! Vous ne pouvez pas éffectuer cette opération");
        };

        ###____SI LA VENTE EST DEJA PASSEE A LA COMPTABILITE ON NE PEUT PLUS LA SUPPRIMER

        if ($request->method() == "GET") {
            ####______VOYONS SI CETTE DEMANDE A DEJA ETE FAITE PAR CE USER
            $isThisDemandOnceMadeByThisUser = false;
            $isThisDemandValidatedForThisUser = false;
            foreach ($vente->_deleteDemandes as $demand) {
                if ($demand->demandeur == auth()->user()->id) {
                    $isThisDemandOnceMadeByThisUser = true;

                    ###___SI LA DEMANDE EST VALIDEE
                    if ($demand->valide) {
                        $isThisDemandValidatedForThisUser = true;
                    }
                }
            }

            # quand la demande a déjà été faite
            if (IsThisVenteDeleteDemandeOnceMade($vente)) {

                ####____SI LA DEMANDE SUPPRIMEE N'EST PAS ACTUELLEMENT VALIDEE
                if (!IsThisVenteUpdateDemandeAlreadyValidated($vente)) {
                    #####________ECRIRE A NOUVEAU UNE DEMANDE DE SUPPRESSION
                    return view("ventes.askDeleteVente", compact('vente'));
                }

                #####____SI C'ETS VALIDEE ON PASSE A LA SUPPRESSION VRAIE
                return view('ventes.delete', compact('vente'));
            } else {

                #####________Faire une demande
                return view("ventes.askDeleteVente", compact('vente'));
            }
        }

        if ($request->method() == "POST") {

            if (!$vente) {
                return redirect()->back()->with("error", "La vente est réquise pour cette requête!");
            }

            Validator::make(
                $request->all(),
                [
                    "raison" => ["required"],
                    "prouve_file" => ["required"],
                ],
                [
                    "raison.required" => "Ce champ est réquis!",
                    "prouve_file.required" => "Ce champ est réquis!",
                ]
            )->validate();

            ###___TRAITEMENT DE L'IMAGE
            if ($request->hasFile("prouve_file")) {
                # code...
                $img = $request->file("prouve_file");
                $img_name = $img->getClientOriginalName();
                $img->move("files", $img_name);

                $prouve_file = asset("/files/" . $img_name);
            }
            ###___

            $data = array_merge($request->all(), [
                "vente" => $vente->id,
                "demandeur" => auth()->user()->id,
                "prouve_file" => $prouve_file,
            ]);

            VenteDeleteDemand::create($data);

            ###___
            return redirect('/ventes/index')->with("message", "Demande de suppression de vente effectuée avec succès! Patientez en attendant que la réquête soit traitée et validée!");
        }
    }

    #####________DELETE VENTES VALIDATIONS
    public function venteDeleteValidation(Request $request)
    {
        if ($request->method() == "POST") {
            $demand = VenteDeleteDemand::find($request->demand);
            if (!$demand) {
                return redirect()->back()->with("error", "Cette demande de modification n'existe pas!");
            }

            ###___MODIFICATION
            $demand->valide = 1;
            $demand->save();
            return redirect()->back()->with("message", "Demande de suppresion validée avec succès!");
        }

        ####___
        $venteDeleteDemands = VenteDeleteDemand::OrderBy("valide", "asc")->get();
        return view("ventes.venteDeleteDemand", compact("venteDeleteDemands"));
    }

    public function DeleteDemandVenteDelete(Request $request, VenteDeleteDemand $demand)
    {
        if (!$demand) {
            return back()->with("error", "Cette demande de suppression n'existe plus!");
        }

        $demand->delete();
        return back()->with("message", "Demande annulée avec succès!");
    }
}
