<?php

namespace App\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationDatabase extends Model
{
    use HasFactory;

    protected $fillable = ['organization_id', 'database_name', 'database_host', 'database_username', 'database_password'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
