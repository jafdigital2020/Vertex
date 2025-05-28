<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'previous_company',
        'designation',
        'date_from',
        'date_to',
        'is_present',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
