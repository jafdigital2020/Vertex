<?php

namespace App\Models;

use App\Models\User;
use App\Models\CoeApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'purpose',
        'recipient_name',
        'recipient_company',
        'address_to',
        'request_date',
        'needed_by_date',
        'status',
        'current_step',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(CoeApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(CoeApproval::class)
            ->latestOfMany('acted_at');
    }
}
