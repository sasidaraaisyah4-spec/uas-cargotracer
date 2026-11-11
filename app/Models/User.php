<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return in_array($this->role, ['operator', 'admin']);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'user_id');
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'created_by');
    }

    public function reportedExceptions()
    {
        return $this->hasMany(ShipmentException::class, 'reported_by');
    }

    public function resolvedExceptions()
    {
        return $this->hasMany(ShipmentException::class, 'resolved_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', false)->latest();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
