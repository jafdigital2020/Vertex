<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatusApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_status',
        'requested_status',
        'remarks',
        'approval_status',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'tenant_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the employee whose status is being changed
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who requested the status change
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/rejected the request
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
