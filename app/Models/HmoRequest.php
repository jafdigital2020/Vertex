<?php

namespace App\Models;

use App\Models\User;
use App\Models\HmoApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HmoRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'hmo_plan',
        'coverage_type',
        'number_of_dependents',
        'dependent_details',
        'effective_date',
        'reason',
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
        return $this->hasMany(HmoApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(HmoApproval::class)
            ->latestOfMany('acted_at');
    }
}
