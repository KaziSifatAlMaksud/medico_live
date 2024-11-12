<?php

namespace Modules\Appointment\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Clinic\Models\ClinicsService;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Clinic\Models\Clinics;
use Modules\Commission\Models\CommissionEarning;
use Modules\Commission\Trait\CommissionTrait;
use Modules\Tip\Trait\TipTrait;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\Receptionist;

class Appointment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use CommissionTrait;
    use TipTrait;

    protected $table = 'appointments';

    const CUSTOM_FIELD_MODEL = 'Modules\Appointment\Models\Appointment';

    protected $fillable = ['status', 'start_date_time','user_id','clinic_id','doctor_id','appointment_extra_info','appointment_date','appointment_time','service_id','total_amount','service_amount','service_price','duration'];

    protected $appends = ['file_url'];

    protected $casts = [
        'user_id' => 'integer',
        'clinic_id' => 'integer',
        'service_id' => 'integer',
        'doctor_id' => 'integer',
        'user_id' => 'integer',
    ];
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Appointment\database\factories\AppointmentFactory::new();
    }
    public function getFileUrlAttribute()
    {
        $media = $this->getFirstMediaUrl('file_url');

        return isset($media) && ! empty($media) ? $media : Null;
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id')->with('commissionData');
    }

    public function doctorData()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id','doctor_id');
    }



    public function clinicservice()
    {
        return $this->belongsTo(ClinicsService::class, 'service_id')->with('systemservice','serviceRating');
    }

    public function appointmenttransaction()
    {
        return $this->hasOne(AppointmentTransaction::class, 'appointment_id');
    }

    public function cliniccenter()
    {
        return $this->belongsTo(Clinics::class, 'clinic_id')->with('vendor');
    } 

    public function payment()
    {
        return $this->hasOne(AppointmentTransaction::class);
    }

    public function commissionsdata()
    {
        return $this->hasMany(CommissionEarning::class,'commissionable_id','id');
    }

    public function receptionist()
    {
        return $this->belongsTo(Receptionist::class, 'clinic_id','clinic_id');
    } 

    public function serviceRating()
    {
        return $this->hasOne(DoctorRating::class, 'service_id', 'service_id');
    } 

    public function patientEncounter()
    {
        return $this->hasOne(PatientEncounter::class, 'appointment_id')->with('billingrecord');
    } 

    public function scopeCheckMultivendor($query){
        if(multiVendor() == "0") {
            $query = $query->whereHas('cliniccenter', function ($q){
                $q->whereHas('vendor', function ($que){
                    $que->whereIn('user_type', ['admin','demo_admin']);
                });
            });
        }
    }

    public function scopeSetRole($query, $user)
    {
       $user_id = $user->id;
       
       if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
           if (multiVendor() == "0") {

            $user_ids = User::role(['admin', 'demo_admin'])->pluck('id');
            
              $query->with('cliniccenter')->whereHas('cliniccenter', function ($query) use ($user_ids) {
                 $query->whereIn('vendor_id', $user_ids);
              });
            }

           return $query; 
      }       
       
       if($user->hasRole('vendor')) {  

            $query->with('cliniccenter')->whereHas('cliniccenter', function ($query) use ($user_id) {

              $query->where('vendor_id', $user_id);

           });
           return $query; 
        }

        if(auth()->user()->hasRole('doctor')) {

           if(multiVendor() == 0) {

               $doctor=Doctor::where('doctor_id',$user_id)->first();

               $vendorId=$doctor->vendor_id;

               $query=$query->where('doctor_id',$user_id)->whereHas('doctorData', function ($qry) use ($vendorId) {

                   $qry->where('vendor_id', $vendorId);

                   });

           }else{

             $query=$query->where('doctor_id',$user_id);

          }

          return $query;
       }

       if (auth()->user()->hasRole('receptionist')) {


           $Receptionist=Receptionist::where('receptionist_id',$user_id)->first();
   
           $vendorId=$Receptionist->vendor_id;
           $clinic_id=$Receptionist->clinic_id;
   
           if(multiVendor() == "0") {

              $query=$query->where('clinic_id',$clinic_id)->whereHas('cliniccenter', function ($qry) use ($vendorId) {

                  $qry->where('vendor_id', $vendorId);

                });    

           }else{

            $query=$query->where('clinic_id',$clinic_id);
            
           }
        // $query=$query->where('clinic_id',$clinic_id);
     
        return $query;
        
       }

       if (auth()->user()->hasRole('user')) {

            $query->where('user_id', $user_id);
            return $query; 
         
        }

    

        return $query;
    }

    public function bodyChart()
    {
        return $this->hasMany(AppointmentPatientBodychart::class,'appointment_id')->with('patient_encounter');
    }


}
