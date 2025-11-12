<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResignationClearanceAuditTrail extends Model
{
   
    protected $table = 'resignation_clearance_audit_trail';
      
    protected $fillable = [
        'resignation_id',
        'asset_detail_id',
        'previous_asset_status',
        'attachment_id',
        'performed_by',
        'action'
    ];

    public $timestamps = true; 
 
}
