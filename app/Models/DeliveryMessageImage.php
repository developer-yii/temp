<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMessageImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_message_id',
        'image_path',
    ];

    public function deliveryMessage()
    {
        return $this->belongsTo(DeliveryMessage::class);
    }
}
