<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = ['name', 'email', 'photo', 'descriptor'];

    protected $casts = [
        'descriptor' => 'array',
    ];
}
