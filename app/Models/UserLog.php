<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'global_user_id',
        'module',
        'action',
        'description',
        'affected_id',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function getActorNameAttribute()
    // {
    //     if ($this->user_id) {
    //         return optional(\App\Models\User::find($this->user_id))->username ?? 'Unknown User';
    //     }

    //     if ($this->global_user_id) {
    //         return optional(\App\Models\GlobalUser::on('mysql_main')->find($this->global_user_id))->username ?? 'Unknown Global User';
    //     }

    //     return 'System';
    // }

    // public function globalUser()
    // {
    //     return \App\Models\GlobalUser::on('mysql_main')->where('id', $this->global_user_id)->first();
    // }
}
