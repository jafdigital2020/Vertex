<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveSetting extends Model
{
   use HasFactory;

   protected $fillable = [
    'leave_type_id',
    'advance_notice_days',
    'allow_half_day',
    'allow_backdated',
    'backdated_days',
    'require_documents',
   ];
}
