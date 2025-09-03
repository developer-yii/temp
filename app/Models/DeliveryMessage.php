<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'delivery_message',
        'added_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function invitedUsers()
    {
        return $this->belongsToMany(User::class, 'delivery_message_users')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }

}
