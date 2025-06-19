<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAccessLevel extends Model
{
    protected $table = 'data_access_level';
    public $timestamps = true;

    protected $fillable = [
        'access_name',  
    ]; 
}
