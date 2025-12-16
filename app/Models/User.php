<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username', 'email', 'password', 'full_name', 'role', 'status'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed', Laravel 10+ auto hash password
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashSessions()
    {
        return $this->hasMany(CashSession::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    // Role Check Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    // Check if user has specific role
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    // Check if user has any of the given roles
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role, $roles);
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for specific role
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}