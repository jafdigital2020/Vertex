<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtTemplateRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'ot_template_id',
        'type',
        'normal',
        'overtime',
        'night_differential',
        'night_differential_overtime',
    ];

    public function otTemplate()
    {
        return $this->belongsTo(OtTemplate::class);
    }
}
