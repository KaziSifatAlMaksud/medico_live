<?php

namespace Modules\Appointment\Models;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\EncounterPrescriptionFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Appointment\Models\PatientEncounter;


class EncounterPrescription extends Model
{
    use HasFactory;
    use SoftDeletes;
    

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'encounter_prescription';
    
    protected $fillable = ['encounter_id','user_id','name','frequency','duration','instruction'];
 
    protected static function newFactory(): EncounterPrescriptionFactory
    {
        //return EncounterPrescriptionFactory::new();
    }

    public function encounter()
    {
        return $this->belongsTo(PatientEncounter::class)->with('clinic','doctor');
    }


    

    
}
