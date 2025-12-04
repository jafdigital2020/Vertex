<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatePermission extends Model
{
    use HasFactory;

    protected $table = 'candidate_permission';

    protected $fillable = [
        'candidate_id',
        'role_id',
        'menu_ids',
        'module_ids',
        'candidate_permission_ids',
        'data_access_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function dataAccessLevel()
    {
        return $this->belongsTo(DataAccessLevel::class, 'data_access_id');
    }
}
