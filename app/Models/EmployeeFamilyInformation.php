<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeFamilyInformation extends Model
{
   use HasFactory;

   protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'birthdate',
        'phone_number',
   ];

   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }

}
