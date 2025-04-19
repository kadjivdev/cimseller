<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recouvrement extends Model
{
    use HasFactory;

    protected $fillable = [
        "client_id",
        "comments",
        "verified",
        "verified_at",
        "verified_by",
        "user_id"
    ];

    function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    function user() {
        
        return $this->belongsTo(User::class, "user_id");
    }

    function verifiedBy() {
        
        return $this->belongsTo(User::class, "verified_by");
    }
}
