<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMessageUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_message_id',
        'user_id',
        'is_read'
    ];
}
