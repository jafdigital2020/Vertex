<?php

namespace App\Models;

use App\Models\User;
use App\Models\DeminimisBenefits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDeminimis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deminimis_benefit_id',
        'amount',
        'benefit_date',
        'taxable_excess'
    ];

    public function deminimisBenefit()
    {
        return $this->belongsTo(DeminimisBenefits::class, 'deminimis_benefit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
