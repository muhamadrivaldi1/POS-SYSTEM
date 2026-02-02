<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* ================= ROLE CHECK ================= */

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * hasRole('owner')
     * hasRole(['owner','supervisor'])
     */
    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }
}
