<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'force_password_reset',
        'profile_photo_path',
        'password_changed_at',
        'first_login_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'timezone',
        'locale',
        'blocked_at',
        'profile_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'force_password_reset' => 'boolean',
            'password_changed_at' => 'datetime',
            'first_login_at' => 'datetime',
            'last_login_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNull('blocked_at');
    }

    public function mustChangePassword(): bool
    {
        return (bool) $this->force_password_reset || ! $this->password_changed_at;
    }

    public function isBlocked(): bool
    {
        return ! is_null($this->blocked_at);
    }
}
