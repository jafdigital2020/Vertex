<?php

namespace App\Models;

use App\Models\User;
use App\Models\BudgetApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'project_name',
        'budget_category',
        'requested_amount',
        'start_date',
        'end_date',
        'justification',
        'expected_outcome',
        'file_attachment',
        'status',
        'current_step',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(BudgetApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(BudgetApproval::class)
            ->latestOfMany('acted_at');
    }
}
