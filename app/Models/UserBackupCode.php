<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBackupCode extends Model
{
    protected $table = 'user_backup_codes';
    public $timestamps = false;
    protected $fillable = ['user_id', 'code', 'used', 'used_at', 'created_at'];
}
