<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by_type',
        'created_by_id',
        'updated_by_type',
        'updated_by_id',
    ];

    public function createdBy()
    {
        return $this->morphTo('created_by');
    }

    public function updatedBy()
    {
        return $this->morphTo('updated_by');
    }

    public function otTemplateRates()
    {
        return $this->hasMany(OtTemplateRate::class);
    }

}
