<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinnedMessage extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'message_id', 'conversation_id', 'created_at'];

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }
}
