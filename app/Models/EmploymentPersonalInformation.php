<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmploymentPersonalInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'birth_place',
        'gender',
        'civil_status',
        'nationality',
        'religion',
        'phone_number',
        'personal_email',
        'complete_address',
        'spouse_name',
        'no_of_children',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Full Name
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
