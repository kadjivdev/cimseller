<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'civilite',
        'nom',
        'prenom',
        'photo',
        'sigle',
        'raisonSociale',
        'logo',
        'telephone',
        'parent',
        'numerocompte',
        'email',
        'adresse',
        'domaine',
        'statutCredit',
        'sommeil',
        'type_client_id',
        'agent_id',
        'credit',
        'filleulFisc',
        'departement_id',
        'bordereau_receit'
    ];

    public function typeclient()
    {
        return $this->belongsTo(TypeClient::class, 'type_client_id', 'id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id', 'id');
    }

    public function _Zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }

    public function vente()
    {
        return $this->hasMany(Vente::class, "client_id");
    }

    public function commandeclients()
    {
        return $this->hasMany(CommandeClient::class, 'client_id', 'id');
    }

    public function compteClients()
    {
        return $this->hasMany(CompteClient::class, 'client_id', 'id');
    }

    public function agents()
    {
        return $this->belongsTo(Agent::class, 'portefeuille');
        /* ->withPivot('datedebut','datefin','statut')
        ->withTimestamps(); */
    }

    public function getFilleulFiscAttribute($value)
    {
        return !is_null($value) ? json_decode($value, true) : [];
    }

    public function _detteReglements()
    {
        return $this->hasMany(DetteReglement::class, 'client', 'id');
    }
    public function _deletedVentes()
    {
        return $this->hasMany(DeletedVente::class, 'ctl_payeur');
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class, 'client_id');
    }

    // 
    public function  Is_Bef()
    {
        return $this->commandeclients()->count() == 0 && $this->debit_old && !in_array($this->id,[959,1624,1971,1721,2079,2028,2141,2334,2115,1940,483,1949,1949,1767,1684,1458,1417,576,572,1435,1669,728]);
    }

    public function  Is_Inactif()
    {
        return $this->commandeclients()->count() == 0 && !$this->debit_old && $this->created_at < "2024-12-31" && !in_array($this->id, [1518,1435,1669,728,890,2482]) ;
    }
}
