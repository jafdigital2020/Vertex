<?php

namespace App\Models;

use App\Models\User;
use App\Models\LoanRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_request_id',
        'approver_id',
        'step',
        'action',
        'comment',
        'acted_at',
    ];

    public function loanRequest()
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
