<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspensionHR extends Model
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


    public function suspension()
    {
        return $this->belongsTo(Suspension::class);
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
