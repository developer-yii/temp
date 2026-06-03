<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'color',
        'created_by',
        'first_visitor'
    ];

    public const COLOR_PALETTE = [
        '#f0f8ff', // AliceBlue
        '#f0fff0', // Honeydew
        '#f0ffff', // Azure
        '#e6f2ff', // Light Sky Blue
        '#fff0f5', // LavenderBlush
        '#f0e6ff', // Light Lavender

        // '#ffe6f0', // Light Pink
        // '#e6ffe6', // Light Green
        // '#fffff0', // Ivory            base on yellow and brown
        // '#fff5ee', // SeaShell         base on yellow and brown
        // '#f5f5dc', // Beige            base on yellow and brown
        // '#faf0e6', // Linen            base on yellow and brown
        // '#fff0e6', // Light Peach      base on yellow and brown
        // '#f5fffa', // MintCream
        // '#fff8f0', // Light Orange     base on yellow and brown
    ];

    public static function getRandomColor($conversationId = null)
    {
        if ($conversationId) {
            $usedColors = self::where('conversation_id', $conversationId)
                ->pluck('color')
                ->toArray();

            $availableColors = array_diff(self::COLOR_PALETTE, $usedColors);

            if (!empty($availableColors)) {
                return $availableColors[array_rand($availableColors)];
            }
        }

        return self::COLOR_PALETTE[array_rand(self::COLOR_PALETTE)];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->color)) {
                $model->color = self::getRandomColor($model->conversation_id);
            }
        });

        static::saving(function ($model) {
            if (empty($model->color) || !preg_match('/^#[0-9a-fA-F]{6}$/', $model->color)) {
                $model->color = self::getRandomColor();
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
