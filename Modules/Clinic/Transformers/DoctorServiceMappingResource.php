<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Clinic\Transformers\ClinicsResource;
use Modules\Appointment\Trait\AppointmentTrait;

class DoctorServiceMappingResource extends JsonResource
{
    use AppointmentTrait;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        
        return [
            'id' => $this->id,
            'service_id'=> $this->service_id,
            'clinic_id'=> $this->clinic_id,
            'doctor_id'=> $this->doctor_id,
            'charges'=> $this->charges,
            'price_detail' => $this->getServiceAmount($this->service_id, $this->doctor_id, $this->clinic_id),
            'name'=> optional($this->clinicservice)->name,
            'doctor_name'=>optional(optional($this->doctors)->user)->full_name,
            'clinic_name'=>optional($this->clinic)->name,
            'doctor_profile'=>optional(optional($this->doctors)->user)->profile_image,
            
        ];
    }
}
