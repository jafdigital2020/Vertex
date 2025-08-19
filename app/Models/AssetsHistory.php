<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetsHistory extends Model
{
    use HasFactory;

    protected $table = 'assets_history';

    protected $fillable = [
        'asset_id',
        'name',
        'description',
        'category_id',
        'branch_id',
        'quantity',
        'price',
        'deployment_date',
        'model',
        'manufacturer',
        'serial_number',
        'processor',
        'process',
        'updated_by',
        'created_by',
    ];
    public $timestamps = true;

    public function asset()
    {
        return $this->belongsTo(Assets::class, 'asset_id');
    } 
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    } 
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }  
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    } 
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function assetsDetails()
    {
        return $this->hasMany(AssetsDetails::class,'asset_id','asset_id');
    }
}
