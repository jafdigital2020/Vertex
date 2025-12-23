<?php

namespace App\Models;

use App\Models\User;
use App\Models\AssetApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'asset_type',
        'asset_name',
        'quantity',
        'estimated_cost',
        'urgency_level',
        'purpose',
        'justification',
        'request_date',
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
        return $this->hasMany(AssetApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(AssetApproval::class)
            ->latestOfMany('acted_at');
    }
}
