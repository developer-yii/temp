<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inviteUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'created_by',
        'first_visitor'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
