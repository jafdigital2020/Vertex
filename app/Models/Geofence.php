<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\GeofenceUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Geofence extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'geofence_name',
        'geofence_address',
        'latitude',
        'longitude',
        'geofence_radius', // This is the radius of the geofence in meters
        'expiration_date', // Date when the geofence expires
        'status', // active, inactive
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

    // Branch relationship
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function geofenceUser()
    {
        return $this->hasMany(GeofenceUser::class);
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
}
