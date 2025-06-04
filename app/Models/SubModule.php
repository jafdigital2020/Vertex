<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubModule extends Model
{
   
    protected $table = 'sub_modules';
    protected $fillable = [
        'sub_module_name', 
        'module_id', 
    ];
    public $timestamps = true;
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }
}
