<?php

namespace App\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_users',
        'price_monthly',
        'price_yearly',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
