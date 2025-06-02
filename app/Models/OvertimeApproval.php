<?php

namespace App\Models;

use App\Models\User;
use App\Models\Overtime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OvertimeApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_id',
        'approver_id',
        'status',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function overtime()
    {
        return $this->belongsTo(Overtime::class);
    }

    public function otApprover()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
