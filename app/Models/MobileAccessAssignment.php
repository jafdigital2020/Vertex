<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MobileAccessAssignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'mobile_access_license_id',
        'branch_id',
        'status',
        'assigned_at',
        'revoked_at',
        'revoke_reason',
        'assigned_by_type',
        'assigned_by_id',
        'revoked_by_type',
        'revoked_by_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the employee that has mobile access.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mobile access license pool this assignment belongs to.
     */
    public function mobileAccessLicense(): BelongsTo
    {
        return $this->belongsTo(MobileAccessLicense::class);
    }

    /**
     * Get the branch this assignment is associated with.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who assigned this mobile access.
     */
    public function assignedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who revoked this mobile access.
     */
    public function revokedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include assignments for a specific tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include revoked assignments.
     */
    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    /**
     * Scope a query to only include assignments for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the assignment is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the assignment is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Revoke this mobile access assignment.
     */
    public function revoke(?string $reason = null, ?Model $revokedBy = null): void
    {
        $this->status = 'revoked';
        $this->revoked_at = now();
        $this->revoke_reason = $reason;

        if ($revokedBy) {
            $this->revoked_by_type = get_class($revokedBy);
            $this->revoked_by_id = $revokedBy->id;
        }

        $this->save();

        // Update the license pool counts
        $this->mobileAccessLicense->updateLicenseCounts();
    }

    /**
     * Reactivate this mobile access assignment (if license is available).
     */
    public function reactivate(?Model $reactivatedBy = null): bool
    {
        if (!$this->mobileAccessLicense->canAssignLicense()) {
            return false;
        }

        $this->status = 'active';
        $this->assigned_at = now();
        $this->revoked_at = null;
        $this->revoke_reason = null;

        if ($reactivatedBy) {
            $this->assigned_by_type = get_class($reactivatedBy);
            $this->assigned_by_id = $reactivatedBy->id;
        }

        // Clear revoked by info
        $this->revoked_by_type = null;
        $this->revoked_by_id = null;

        $this->save();

        // Update the license pool counts
        $this->mobileAccessLicense->updateLicenseCounts();

        return true;
    }

    /**
     * Get the duration of this assignment (in days).
     */
    public function getDurationInDays(): int
    {
        $endDate = $this->revoked_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Update license counts when an assignment is created, updated, or deleted
        static::created(function ($assignment) {
            $assignment->mobileAccessLicense->updateLicenseCounts();
        });

        static::updated(function ($assignment) {
            $assignment->mobileAccessLicense->updateLicenseCounts();
        });

        static::deleted(function ($assignment) {
            $assignment->mobileAccessLicense->updateLicenseCounts();
        });
    }
}