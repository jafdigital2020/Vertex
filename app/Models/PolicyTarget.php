<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PolicyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'target_type',
        'target_id',   // ID of the user or department
    ];

    // Policy relationship
    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    // Target relationship (can be user or department)
    public function target()
    {
        if ($this->target_type === 'user') {
            return $this->belongsTo(User::class, 'target_id');
        } elseif ($this->target_type === 'department') {
            return $this->belongsTo(Department::class, 'target_id');
        } else if ($this->target_type === 'branch') {
            return $this->belongsTo(Branch::class, 'target_id');
        } else {
            return null;
        }
    }

    public function getTargetNameAttribute()
    {
        switch ($this->target_type) {
            case 'employee': // or 'user'
            case 'user':
                return optional(\App\Models\User::find($this->target_id))
                    ->personalInformation->full_name ?? 'No name available';
            case 'department':
                return optional(\App\Models\Department::find($this->target_id))->department_name;
            case 'branch':
                return optional(\App\Models\Branch::find($this->target_id))->name;
            case 'organization':
                return optional(\App\Models\Tenant::find($this->target_id))->name;
            default:
                return '';
        }
    }
}
