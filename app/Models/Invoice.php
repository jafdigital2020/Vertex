<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'branch_subscription_id',
        'invoice_number',
        'amount_due',
        'amount_paid',
        'currency',
        'due_date',
        'status',
        'issued_at',
        'paid_at',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'amount_due'     => 'decimal:2',
        'amount_paid'    => 'decimal:2',
        'due_date'       => 'date',
        'issued_at'      => 'datetime',
        'paid_at'        => 'datetime',
        'period_start'   => 'date',
        'period_end'     => 'date',
    ];

    // --- Relationships ---
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function subscription()
    {
        return $this->belongsTo(BranchSubscription::class, 'branch_subscription_id');
    }

    // --- Derived attributes ---
    public function getBalanceAttribute()
    {
        return max(0, (float)$this->amount_due - (float)$this->amount_paid);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', now()->toDateString());
    }

    // --- Helpers ---
    public function markSent(): void
    {
        $this->status = 'sent';
        $this->issued_at = $this->issued_at ?? now();
        $this->save();
    }

 
}
