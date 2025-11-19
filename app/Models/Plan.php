<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'employee_minimum',
        'employee_limit',
        'employee_price',
        'trial_days',
        'is_active',
        'price_per_license', // New field for price per additional license
        'implementation_fee', // New field for integration fee
        'vat_percentage', // New field for VAT percentage
        'base_license_count', // New field for base license count
        'inclusions', // New field for inclusions
    ];


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
