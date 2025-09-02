<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'addon_key',
        'name',
        'price',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];


    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_addons')
            ->withPivot('active', 'start_date', 'end_date')
            ->withTimestamps();
    }
}
