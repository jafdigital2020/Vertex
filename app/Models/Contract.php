<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'contract_type',
        'content',
        'start_date',
        'end_date',
        'status',
        'signed_date',
        'signed_by',
        'tenant_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
    ];

    /**
     * Get the employee that owns the contract
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used for this contract
     */
    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    /**
     * Get the user who signed the contract
     */
    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Scope to get active contracts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope to get probationary contracts
     */
    public function scopeProbationary($query)
    {
        return $query->where('contract_type', 'Probationary');
    }

    /**
     * Scope to get regular contracts
     */
    public function scopeRegular($query)
    {
        return $query->where('contract_type', 'Regular');
    }

    /**
     * Check if contract is expired
     */
    public function isExpired()
    {
        if (!$this->end_date) {
            return false;
        }
        
        return now()->greaterThan($this->end_date);
    }
}
