<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetailsAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attachment_name',
        'attachment_path',
        'upload_by_type',
        'upload_by_id',
    ];

    // User relationship
    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }

    // Get the path to the attachment
    public function getAttachmentPathAttribute($value)
    {
        return asset('storage/' . $value);
    }

    // Morph relationship for upload_by
    public function uploadBy()
    {
        return $this->morphTo();
    }

    public function getCreatorNameAttribute()
    {
        if ($this->uploadBy instanceof \App\Models\User) {
            return $this->uploadBy->personalInformation->full_name ?? 'Unnamed User';
        }

        if ($this->uploadBy instanceof \App\Models\GlobalUser) {
            return $this->uploadBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }
}
