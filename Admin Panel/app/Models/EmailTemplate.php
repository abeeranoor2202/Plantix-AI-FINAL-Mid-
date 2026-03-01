<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['type', 'subject', 'body', 'is_active', 'firebase_doc_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
