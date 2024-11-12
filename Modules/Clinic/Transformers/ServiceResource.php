<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Clinic\Transformers\ClinicServiceMappingResource;
use Modules\Clinic\Transformers\DoctorServiceMappingResource;



class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {  
        $discount_amount = 0;

        if($this->discount == 1){
           $discount_amount = ( $this->discount_type == 'percentage') 
                                ? $this->charges * $this->discount_value / 100 
                                : $this->discount_value;
         }

        if(auth()->user() && auth()->user()->hasRole('doctor')){
            $doctor_service =  $this->doctor_service->where('doctor_id',auth()->user()->id);
        }
        else{
            $doctor_service = $this->doctor_service;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'system_service_id'=>$this->id,
            'slug' => $this->slug,
            'description' => $this->description,
            'charges' => $this->charges,
            'status' => $this->status,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'vendor_id' => $this->vendor_id,
            'category_name' => optional($this->category)->name ?? null,
            'subcategory_name' => optional($this->sub_category)->name ?? null,
            'duration' => $this->duration_min,
            'discount'=> $this->discount,
            'featured'=>optional($this->systemservice)->featured,
            'discount_type'=>$this->discount_type,
            'discount_value'=>$this->discount_value,
            'discount_amount'=>$discount_amount,
            'payable_amount'=> $this->charges-$discount_amount,
            'assign_doctor'=>DoctorServiceMappingResource::collection($doctor_service),
            'time_slot'=>$this->time_slot,
            'is_video_consultancy' => $this->is_video_consultancy,
            'type'=>$this->type,
            'vendor_name'=>optional($this->vendor)->full_name,
            'service_image' => $this->file_url,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
