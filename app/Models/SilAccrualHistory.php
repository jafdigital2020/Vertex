<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SilAccrualHistory extends Model
{
    protected $table = 'sil_accrual_history';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'accrual_date',
        'days_credited',
        'service_years',
        'employment_date',
        'anniversary_date',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'accrual_date' => 'date',
        'employment_date' => 'date',
        'anniversary_date' => 'date',
        'days_credited' => 'decimal:2',
        'service_years' => 'integer',
    ];

    /**
     * Get the user this accrual belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type this accrual is for
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
