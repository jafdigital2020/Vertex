<?php

namespace App\Models;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{

    protected $table = 'tenants';
    public $timestamps = true;


    // holiday relationship
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

}
