<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationTypes extends Model
{
    protected $table = 'violation_types';

    public $timestamps = true;
    protected $fillable = [
         'name'
    ]; 
}
