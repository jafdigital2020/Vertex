<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    use HasFactory;

    protected $table = 'password_otps';

    protected $fillable = [
        'email',
        'otp_hash',
        'expires_at',
    ];

    // ⬅ modern Laravel casting
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
