<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'normal',
        'overtime',
        'night_differential',
        'night_differential_overtime',
    ];
}
