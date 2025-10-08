<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //
    use HasFactory;


    protected $fillable = [
        'user_id',
        'device_name',
        'device_identifier',
    ];

    /**
     * Relationship: Device belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
