<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Model;

class RevokedToken extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'token_hash',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
