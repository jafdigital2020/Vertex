<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetsDetailsHistory extends Model
{
    use HasFactory;

    protected $table = 'assets_details_history';

    protected $fillable = [
        'asset_detail_id',
        'item_no',
        'deployed_to',
        'deployed_date',
        'condition',
        'status',
        'condition_remarks',
        'status_remarks',
        'process',
        'updated_by',
        'created_by',
    ];

    protected $dates = [
        'deployed_date',
        'created_at',
        'updated_at',
    ];

    public function assetDetail()
    {
        return $this->belongsTo(AssetsDetails::class, 'asset_detail_id');
    }

    public function deployedToEmployee()
    {
        return $this->belongsTo(User::class, 'deployed_to');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
     

}
