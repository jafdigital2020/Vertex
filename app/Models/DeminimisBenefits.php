<?php

namespace App\Models;

use App\Models\UserDeminimis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeminimisBenefits extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'maximum_amount',
        'frequency',
    ];

    public function userDeminimis()
    {
        return $this->hasMany(UserDeminimis::class, 'deminimis_benefit_id');
    }
}
