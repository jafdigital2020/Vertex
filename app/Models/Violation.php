<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;

    protected $fillable  = [
            'user_id',
            'offense_details',
            'information_report_file',
            'dam_file',
            'dam_issued_at',
            'disciplinary_action',
            'violation_type_id',
            'violation_start_date',
            'violation_end_date',
            'violation_days',
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
        return $this->hasMany(ViolationHR::class);
    }

    // Timeline or actions related to this suspension
    public function actions()
    {
        return $this->hasMany(ViolationAction::class);
    }

    // Attachments for this violation
    public function attachments()
    {
        return $this->hasMany(ViolationAttachment::class);
    }
    public function violationType()
    {
        return $this->hasOne(ViolationTypes::class,'id','violation_type_id');
    }

}
