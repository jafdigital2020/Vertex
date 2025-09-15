<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenseUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'subscription_id',
        'subscription_period_start',
        'subscription_period_end',
        'activated_at',
        'deactivated_at',
        'is_billable',
        'overage_rate'
    ];

    protected $casts = [
        'subscription_period_start' => 'date',
        'subscription_period_end' => 'date',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'is_billable' => 'boolean',
        'overage_rate' => 'decimal:2'
    ];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Subscription Relationship
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Tenant Relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get usage logs for a specific subscription period
     */
    public static function getUsageForPeriod($tenantId, $periodStart, $periodEnd)
    {
        return static::where('tenant_id', $tenantId)
            ->where('subscription_period_start', $periodStart)
            ->where('subscription_period_end', $periodEnd)
            ->where('is_billable', true)
            ->get();
    }

    /**
     * Get billable licenses count for a subscription period
     */
    public static function getBillableLicensesCount($tenantId, $periodStart, $periodEnd)
    {
        return static::where('tenant_id', $tenantId)
            ->where('subscription_period_start', $periodStart)
            ->where('subscription_period_end', $periodEnd)
            ->where('is_billable', true)
            ->distinct('user_id')
            ->count();
    }
}
