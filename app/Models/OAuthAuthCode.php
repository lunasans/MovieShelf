<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthAuthCode extends Model
{
    protected $connection   = 'central';
    protected $table        = 'oauth_auth_codes';
    protected $primaryKey   = 'code';
    protected $keyType      = 'string';
    public    $incrementing = false;

    protected $fillable = ['code', 'user_id', 'client_id', 'redirect_uri', 'used', 'expires_at', 'code_challenge', 'code_challenge_method'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
