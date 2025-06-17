<?php

namespace App\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'prefix_name',
        'remarks',
    ];


    // Tenant Relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
