<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CRUD extends Model
{
   
    protected $table = 'crud';
    protected $fillable = [
        'control_name',  
    ];
    public $timestamps = true;
   
}
