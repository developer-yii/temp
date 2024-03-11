<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $table = 'conversations';
    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))->format('d/m/Y');
    }
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($conversation) {
            // Efficiently delete all related messages in one query
            $conversation->messages()->delete();
        });
    }

    // Optionally, if you want to automatically load messages with every conversation
    // protected $with = ['messages'];
}
