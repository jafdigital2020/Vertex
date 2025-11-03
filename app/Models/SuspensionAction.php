<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspensionAction extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'suspension_id',
        'action_type',
        'action_by',
        'action_date',
        'file_path',
        'description',
        'remarks',
    ];

    public function suspension()
    {
        return $this->belongsTo(Suspension::class);
    }

    // HR or user who performed the action
    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
    
}
