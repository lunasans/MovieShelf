<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $fillable = ['user_id', 'action', 'ip_address', 'user_agent'];
}
