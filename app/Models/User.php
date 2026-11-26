<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'job_title',
        'department',
        'phone',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isGM(): bool
    {
        return $this->role === 'gm';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function isOperations(): bool
    {
        return $this->role === 'operations';
    }

    public function isWarehouse(): bool
    {
        return $this->role === 'warehouse';
    }

    public function isPurchasing(): bool
    {
        return $this->role === 'purchasing';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isMaintenance(): bool
    {
        return $this->role === 'maintenance';
    }

    public function isTopManagement(): bool
    {
        return in_array($this->role, ['owner', 'gm']);
    }

    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    public function getRoleArabicNameAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'المالك / صاحب الشركة',
            'gm' => 'المدير العام',
            'sales' => 'مدير المبيعات',
            'operations' => 'مدير التشغيل والإنتاج',
            'warehouse' => 'مدير المخازن',
            'purchasing' => 'مدير المشتريات',
            'finance' => 'مدير الحسابات والمالية',
            'maintenance' => 'مدير الصيانة',
            default => 'مستخدم النظام',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=008060&color=fff&size=128';
    }
}
