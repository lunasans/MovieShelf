<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    protected $primaryKey = 'client_id';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['client_id', 'client_secret', 'name', 'redirect_uri', 'is_active'];
}
