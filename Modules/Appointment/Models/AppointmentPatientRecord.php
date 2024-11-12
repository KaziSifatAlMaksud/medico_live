<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\AppointmentPatientRecordFactory;

class AppointmentPatientRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'appointment_patient_records';

    const CUSTOM_FIELD_MODEL = 'Modules\Appointment\Models\Appointment';

    protected $fillable = ['appointment_id','patient_id','encounter_id', 'subjective','objective','assessment','plan'];



    protected static function newFactory(): AppointmentPatientRecordFactory
    {
        //return AppointmentPatientRecordFactory::new();
    }
}
