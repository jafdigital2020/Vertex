<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'branch_id',
        'quantity',
        'price', 
        'deployment_date',
        'model',
        'manufacturer',
        'serial_number',
        'processor'
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeAssets()
    {
        return $this->hasMany(EmployeeAssets::class);
    }
    public function assetsDetails()
    {
        return $this->hasMany(AssetsDetails::class,'asset_id');
    }

}
