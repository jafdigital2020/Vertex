<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EmploymentPersonalInformation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resignation extends Model
{ 
    use HasFactory;

    protected $table = 'resignations';
     
    protected $fillable = [
        'date_filed',
        'user_id',
        'resignation_file',
        'reason',
        'resignation_date',  
        'status',
        'status_remarks',
        'status_date',
        'accepted_by',
        'accepted_date',
        'accepted_remarks',
        'instruction',
        'cleared_status',
        'cleared_by',
        'cleared_date'
    ];

    public $timestamps = true;
    public function personalInformation()
    {
        return $this->hasOne(EmploymentPersonalInformation::class, 'user_id','user_id');
    }
    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class,'user_id','user_id');
    }   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function resignationAttachment()
    {
        return $this->hasMany(ResignationAttachment::class, 'resignation_id', 'id');
    }

    public function hrResignationAttachments()
    {
        return $this->hasMany(ResignationAttachment::class, 'resignation_id', 'id')
                    ->where('uploader_role', 'hr');
    }

    public function deployedAssets(){

          return $this->hasMany( AssetsDetails::class, 'deployed_to', 'user_id');
    }

}
