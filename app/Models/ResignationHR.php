<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResignationHr extends Model
{
    use HasFactory;
 
    protected $table = 'resignation_hr';
  
    protected $fillable = [
        'tenant_id',
        'hr_id',
        'assigned_by',
        'assigned_at',
        'status',
    ];
 
    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id','id');
    }
 
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by','id');
    }
}
