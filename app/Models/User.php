<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Role type constants
     */
    public const ROLE_SUPER_ADMIN = 0;
    public const ROLE_ADMIN = 1;
    public const ROLE_USER = 2;

    public const ROLE_LABELS = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_USER => 'User',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',
        'is_suggestable',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : '';
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_id', 'id');
    }

    public function userImages()
    {
        return $this->hasMany(UserImage::class, 'user_id', 'id');
    }

    public function deliveryMessages()
    {
        return $this->belongsToMany(DeliveryMessage::class, 'delivery_message_users')
            ->withPivot('is_read')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role_type === self::ROLE_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_type === self::ROLE_SUPER_ADMIN;
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role_type, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function canAccessUserPanel(): bool
    {
        return in_array($this->role_type, [self::ROLE_USER, self::ROLE_ADMIN]);
    }

    public function canAssignAdminRoles(): bool
    {
        return in_array($this->role_type, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->messages()->delete();
            $user->conversations()->delete();
            $user->userImages()->delete();
        });
    }
}
