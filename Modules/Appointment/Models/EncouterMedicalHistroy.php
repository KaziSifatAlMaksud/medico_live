<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\EncouterMedicalHistroyFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class EncouterMedicalHistroy extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'encounter_medical_history';
    
    protected $fillable = ['encounter_id','user_id','type','title','is_from_template'];

    protected $casts = [
        
        'encounter_id' => 'integer',
        'user_id' => 'integer',
        'is_from_template' => 'integer',
       
    ];
    
    protected static function newFactory(): EncouterMedicalHistroyFactory
    {
        //return EncouterMedicalHistroyFactory::new();
    }
}
