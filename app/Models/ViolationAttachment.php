<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_type',
        'uploaded_by',
        'description'
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
