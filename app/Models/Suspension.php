<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    //
    use HasFactory;

    protected $fillable  = [
            'user_id',
            'offense_details',
            'information_report_file',
            'dam_file',
            'dam_issued_at',
            'disciplinary_action',
            'suspension_type',
            'suspension_start_date',
            'suspension_end_date',
            'suspension_days',
            'implemented_by',
            'implementation_remarks',
            'return_to_work_at',
            'status',
            'remarks'
    ];


    // Employee involved in the case
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // HR who implemented the suspension
    public function implementer()
    {
        return $this->belongsTo(User::class, 'implemented_by');
    }

    // HR assignments (many HRs can be assigned)
    public function hrAssignments()
    {
        return $this->hasMany(SuspensionHR::class);
    }

    // Timeline or actions related to this suspension
    public function actions()
    {
        return $this->hasMany(SuspensionAction::class);
    }
}
