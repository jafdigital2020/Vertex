<?php

namespace App\Models;

use App\Models\User;
use App\Models\HmoRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HmoApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'hmo_request_id',
        'approver_id',
        'step',
        'action',
        'comment',
        'acted_at',
    ];

    public function hmoRequest()
    {
        return $this->belongsTo(HmoRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
