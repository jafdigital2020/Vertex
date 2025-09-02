<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;



class BranchAddon extends Model
{
    //
    use HasFactory;

    protected $table = 'branch_addons';

    protected $fillable = [
        'branch_id',
        'addon_id',
        'active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
