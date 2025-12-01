<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationHR extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'suspension_id',
        'hr_id',
        'assigned_by',
        'assigned_at',
        'status',
        'remarks',
    ];


    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id');
    }
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
