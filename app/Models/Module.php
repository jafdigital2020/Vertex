<?php

namespace App\Models;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
   
    protected $table = 'modules';
    protected $fillable = [
        'module_name',  
    ];
    public $timestamps = true;
   
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
