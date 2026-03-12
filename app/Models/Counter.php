<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    protected $table = 'counter';
    protected $fillable = ['id', 'page', 'visits', 'last_visit'];
}
