<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationAction extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'violation_id',
        'action_type',
        'action_by',
        'action_date',
        'file_path',
        'description',
        'remarks',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    // HR or user who performed the action
    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
    
}
