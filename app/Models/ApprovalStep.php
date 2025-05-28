<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'level',
        'approver_kind',
        'approver_user_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
