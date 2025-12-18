<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MobileAccessLicense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'total_licenses',
        'used_licenses',
        'available_licenses',
        'license_price',
        'notes',
        'status',
        'created_by_type',
        'created_by_id',
        'updated_by_type',
        'updated_by_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'license_price' => 'decimal:2',
        'total_licenses' => 'integer',
        'used_licenses' => 'integer',
        'available_licenses' => 'integer',
    ];

    /**
     * Get all mobile access assignments for this license pool.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(MobileAccessAssignment::class);
    }

    /**
     * Get active mobile access assignments for this license pool.
     */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(MobileAccessAssignment::class)->where('status', 'active');
    }

    /**
     * Get the user who created this license pool.
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who last updated this license pool.
     */
    public function updatedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include licenses for a specific tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include active licenses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the percentage of licenses used.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_licenses <= 0) {
            return 0;
        }

        return round(($this->used_licenses / $this->total_licenses) * 100, 2);
    }

    /**
     * Check if there are available licenses.
     */
    public function hasAvailableLicenses(): bool
    {
        return $this->available_licenses > 0;
    }

    /**
     * Calculate the cost for a number of licenses.
     */
    public function calculateCost(int $licenseCount): float
    {
        return $this->license_price * $licenseCount;
    }

    /**
     * Update license counts based on current assignments.
     */
    public function updateLicenseCounts(): void
    {
        $this->used_licenses = $this->activeAssignments()->count();
        $this->available_licenses = $this->total_licenses - $this->used_licenses;
        $this->save();
    }

    /**
     * Add licenses to the pool.
     */
    public function addLicenses(int $count, ?Model $addedBy = null): void
    {
        $this->total_licenses += $count;
        $this->available_licenses += $count;
        
        if ($addedBy) {
            $this->updated_by_type = get_class($addedBy);
            $this->updated_by_id = $addedBy->id;
        }
        
        $this->save();
    }

    /**
     * Check if a license can be assigned (has available licenses).
     */
    public function canAssignLicense(): bool
    {
        return $this->status === 'active' && $this->hasAvailableLicenses();
    }
}