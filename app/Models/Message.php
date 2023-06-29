<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'conversation_token','url','message', 'expiry','link_visit_count', 'created_at',
    ];

    

}
