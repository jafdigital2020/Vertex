<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResignationAttachmentRemarks extends Model
{  
    use HasFactory;

    protected $table = 'resignation_attachment_remarks';
      
    protected $fillable = [
        'resignation_attachment_id',
        'remarks_from',
        'remarks_from_role',
        'remarks',
    ];
     public function personalInformation()
    {
        return $this->hasOne(EmploymentPersonalInformation::class, 'user_id','remarks_from');
    }


}
