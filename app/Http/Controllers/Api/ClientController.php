<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(){
        $clients = Client::all('id','raisonSociale');
        return response($clients);
    }

    public function Retrieve(Request $request,$id){
        $client = Client::find($id);
        return response($client);
    }
}
