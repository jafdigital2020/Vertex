<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BranchAddon extends Model
{
    use HasFactory;

    protected $table = 'branch_addons';

    protected $fillable = [
        'branch_id',
        'addon_id',
        'active',
        'start_date',
        'end_date',
        'feature_type',
        'billing_cycle',
        'price_paid',
        'invoice_id',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'price_paid' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $appends = ['is_expired', 'days_remaining'];

    /**
     * Get the branch that owns the addon
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the addon details
     */
    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    /**
     * Get the invoice associated with this addon purchase
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Check if the addon is expired
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->end_date) {
            return false;
        }

        return Carbon::parse($this->end_date)->isPast();
    }

    /**
     * Get days remaining until expiration
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $endDate = Carbon::parse($this->end_date);

        if ($endDate->isPast()) {
            return 0;
        }

        return Carbon::now()->diffInDays($endDate);
    }

    /**
     * Scope for active addons
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            });
    }

    /**
     * Scope for expired addons
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now());
    }

    /**
     * Deactivate the addon
     */
    public function deactivate()
    {
        $this->update([
            'active' => false,
            'end_date' => Carbon::now(),
        ]);
    }

    /**
     * Renew the addon
     */
    public function renew($billingCycle = 'monthly')
    {
        $startDate = Carbon::now();
        $endDate = $billingCycle === 'yearly'
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();

        $this->update([
            'active' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'billing_cycle' => $billingCycle,
        ]);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->feature_type && $model->addon) {
                $model->feature_type = $model->addon->addon_category;
            }
        });
    }
}
