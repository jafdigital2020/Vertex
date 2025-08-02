<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentGovernmentId extends Model
{
   use HasFactory;

   protected $fillable = [
        'user_id',
        'sss_number',
        'sss_attachment',
        'tin_number',
        'tin_attachment',
        'philhealth_number',
        'philhealth_attachment',
        'pagibig_number',
        'pagibig_attachment',
   ];

   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
}
