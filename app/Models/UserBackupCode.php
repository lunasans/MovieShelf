<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBackupCode extends Model
{
    protected $table = 'user_backup_codes';
    protected $fillable = ['user_id', 'code', 'used_at'];
}
