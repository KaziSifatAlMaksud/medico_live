<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\EncounterMedicalReportFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class EncounterMedicalReport extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */

     protected $table = 'encounter_medical_report';
    
     protected $fillable = ['encounter_id','user_id','name','date'];

     protected $appends = ['file_url'];

    
    protected static function newFactory(): EncounterMedicalReportFactory
    {
        //return EncounterMedicalReportFactory::new();
    }

    protected function getFileUrlAttribute()
    {
        $media = $this->getFirstMediaUrl('file_url');

        return isset($media) && ! empty($media) ? $media : default_file_url();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }



}
