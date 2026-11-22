<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_number',
        'commercial_register',
        'logo',
        'payment_terms',
        'notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://'))) {
            return $this->logo;
        }
        if ($this->logo && file_exists(public_path($this->logo))) {
            return asset($this->logo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0f766e&color=fff&size=128';
    }
}
