<?php

namespace App\Models;

use App\Models\User;
use App\Models\CoeRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoeApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'coe_request_id',
        'approver_id',
        'step',
        'action',
        'comment',
        'acted_at',
    ];

    public function coeRequest()
    {
        return $this->belongsTo(CoeRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
