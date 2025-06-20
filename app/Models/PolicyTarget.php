<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PolicyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'target_type', // e.g., 'user', 'department'
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
        }
    }
}
