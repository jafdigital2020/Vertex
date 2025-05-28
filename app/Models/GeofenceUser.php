<?php

namespace App\Models;

use App\Models\Geofence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeofenceUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'geofence_id',
        'assignment_type', // 'manual' for addition, 'exempt' for opt-out
        'created_by_type', // e.g 'user', 'global_user'
        'created_by_id', // ID of the user or global user who created the shift
        'updated_by_type', // e.g 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the shift
    ];

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
    }

    public function getCreatorNameAttribute()
    {
        if ($this->createdBy instanceof \App\Models\User) {
            return $this->createdBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->createdBy instanceof \App\Models\GlobalUser) {
            return $this->createdBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }

    public function getUpdaterNameAttribute()
    {
        if ($this->updatedBy instanceof \App\Models\User) {
            return $this->updatedBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->updatedBy instanceof \App\Models\GlobalUser) {
            return $this->updatedBy->username ?? 'Unnamed Global User';
        }

        return 'N/A';
    }

    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
