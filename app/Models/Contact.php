<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'is_read'];

    protected $casts = ['is_read' => 'boolean', 'created_at' => 'datetime'];
}
