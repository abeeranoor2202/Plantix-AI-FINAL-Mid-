<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'type', 'email_type', 'subject', 'body', 'variables', 'is_active', 'firebase_doc_id', 'is_send_to_admin'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
