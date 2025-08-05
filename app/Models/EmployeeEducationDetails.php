<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEducationDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'institution_name',
        'course_or_level',
        'date_from',
        'date_to',
        'education_level',
        'year',
        'notes',
        'attachment',
    ];

    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }
}
