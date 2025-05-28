<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'primary_name',
        'primary_relationship',
        'primary_phone_one',
        'primary_phone_two',
        'secondary_name',
        'secondary_relationship',
        'secondary_phone_one',
        'secondary_phone_two',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
