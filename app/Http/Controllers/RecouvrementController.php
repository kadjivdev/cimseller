<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Recouvreur;
use App\Models\Client;
use App\Models\Recouvrement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class RecouvrementController extends Controller
{
    function index(Request $request)
    {
        // clients
        $clients = collect();
        Client::chunk(100, function ($chunk) use (&$clients) {
            $clients = $clients->merge($chunk); //merge the chunk
        });

        // Seuls les clients actifs sont pris en compte
        $clients = $clients->filter(function ($client) {
            return (!$client->Is_Inactif() && !$client->Is_Bef());
        });

        // recouvrements
        $recouvrements = collect();
        Recouvrement::chunk(100, function ($chunk) use (&$recouvrements) {
            $recouvrements = $recouvrements->merge($chunk); //merge du chunk
        });

        if ($request->client) {
            $recouvrements = $recouvrements->where("client_id", $request->client);
        }

        // 
        return view("client.recouvrements.index", compact("recouvrements", "clients"));
    }

    /**
     * Enregistrement d'un recouvrement
     */

    function store(Request $request)
    {
        $request->validate([
            "client_id" => ["required"],
            "comments" => ["required"]
        ], [
            "client_id" => "Le client est réquis",
            "comments" => "Le Commenataire est réquis",
        ]);

        Recouvrement::create($request->all());

        return redirect()->back()->with("success", "Enregistrement éffectué avec succès!");
    }

    /**
     * Validation d'un recouvrement
     */

    function verification(Request $request)
    {
        $request->validate([
            "recouvrements" => ["required"],
        ], [
            "recouvrements.required" => "Choisissez au moins un recouvrement"
        ]);

        $recouvrements = Recouvrement::whereIn("id", $request->recouvrements);
        $recouvrements->update(["verified"=>true]);

        return redirect()->route("recouvrement.index")->with("message", "Recouvrement vérifié avec succès!");
    }
}
