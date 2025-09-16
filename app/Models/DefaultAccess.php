<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultAccess extends Model
{
    use HasFactory;

    protected $table = 'default_access';

    protected $fillable = [
        'submodule_ids',
        'effectivity_date',
    ];

    protected $casts = [
        'effectivity_date' => 'date',
    ];
}
