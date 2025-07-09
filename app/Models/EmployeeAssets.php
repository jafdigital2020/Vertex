<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAssets extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'quantity',
        'price',
        'assigned_at',
        'status',
    ];

    // Since no foreign key constraint on user_id, just define relation if User model exists
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asset()
    {
        return $this->belongsTo(Assets::class);
    }
}
