<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'policy_title',
        'effective_date',
        'policy_content',
        'attachment_path',
        'created_by',
    ];

    // tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // created by relationship
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // policy targets relationship
    public function targets()
    {
        return $this->hasMany(PolicyTarget::class, 'policy_id', 'id');
    }
}
