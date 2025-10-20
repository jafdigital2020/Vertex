<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResignationAttachment extends Model
{
    use HasFactory;

    protected $table = 'resignation_attachment';
      
    protected $fillable = [
        'resignation_id',
        'uploaded_by',
        'uploader_role',
        'filename',
        'filepath',
        'filetype',
    ];
}
