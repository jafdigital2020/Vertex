<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'addon_key',
        'name',
        'price',
        'type',
        'description',
        'is_active',
        'module_ids',
        'submodule_ids',
        'features',
        'icon',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get branches that have this addon
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_addons')
            ->withPivot('active', 'start_date', 'end_date', 'billing_cycle', 'price_paid')
            ->withTimestamps();
    }

    /**
     * Get all branch addons for this addon
     */
    public function branchAddons()
    {
        return $this->hasMany(BranchAddon::class);
    }

    /**
     * Scope for active addons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get yearly price with discount
     */
    public function getYearlyPriceAttribute()
    {
        return $this->price * 12 * 0.9; // 10% discount
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPriceAttribute()
    {
        return '₱' . number_format($this->yearly_price, 2);
    }
}
