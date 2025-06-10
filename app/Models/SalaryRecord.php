<?php

namespace App\Models;

use App\Models\User;
use App\Models\SalaryDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'basic_salary',
        'effective_date',
        'is_active',
        'created_by_id',
        'created_by_type',
        'remarks',
        'salary_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    // ✅ Accessor to get the correct name/username
    public function getCreatorNameAttribute()
    {
        if ($this->createdBy instanceof \App\Models\User) {
            return $this->createdBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->createdBy instanceof \App\Models\GlobalUser) {
            return $this->createdBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }

    public function salaryDetail()
    {
        return $this->hasOne(SalaryDetail::class, 'salary_id');
    }
}
