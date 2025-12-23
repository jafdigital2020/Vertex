<?php

namespace App\Models;

use App\Models\User;
use App\Models\AssetRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_request_id',
        'approver_id',
        'step',
        'action',
        'comment',
        'acted_at',
    ];

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
