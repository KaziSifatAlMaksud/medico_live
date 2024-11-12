<?php

namespace Modules\Appointment\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Transformers\DoctorReviewResource;
use Modules\Appointment\Transformers\BodyChartResource;
use Modules\Appointment\Transformers\BillingItemResource;
use Modules\Appointment\Models\BillingRecord;
class AppointmentDetailResource extends JsonResource
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
        $tax_data = $this->calculateTaxAmounts($this->appointmenttransaction ? $this->appointmenttransaction->tax_percentage: null, ($this->service_amount));
        $total_tax = array_sum(array_column($tax_data, 'amount'));

        $totalAmount = $this->total_amount;
        
        
        $billingItems = optional(optional($this->patientEncounter)->billingrecord)->billingItem ?? collect();
        $billing_record = optional($this->patientEncounter)->billingrecord ?? collect();
        
        if($billing_record instanceof BillingRecord && $billingItems && !empty($billingItems)){
            $service_price = $billingItems->sum('total_amount'); 
        }else{
            $service_price = $this->service_amount;
        }
        if ($billing_record instanceof BillingRecord) {
            $discount_type = $billing_record->final_discount_type ?? '';
            $enable_discount = $billing_record->final_discount ?? 0;
            $total_discount = $billing_record->final_discount_value ?? 0;
        
            if ($discount_type === 'percentage') {
                $billing_discount_total_amount = $billingItems->sum('total_amount') * $total_discount / 100;
            } else {
                $billing_discount_total_amount = $total_discount;
            }
            $totl_amount = $billingItems->sum('total_amount') - $billing_discount_total_amount;
        }     
        if($billing_record instanceof BillingRecord){
            $total_amount = $totl_amount;
            $subtotal = $total_amount;
            $tax_data = json_encode($tax_data);
            $tax_data = $this->calculateTaxAmounts($tax_data ?? null, $total_amount);
            $total_tax = array_sum(array_column($tax_data, 'amount'));
            $totalAmount = $total_amount + $total_tax;
        }else{
            $subtotal = $this->service_amount;
        }

        return [
            'id'=> $this->id,
            'status'=> $this->status,
            'start_date_time'=> $this->start_date_time,
            'user_id'=> $this->user_id,
            'user_name'=>optional($this->user)->first_name .' '.optional($this->user)->last_name,
            'user_image'=>optional($this->user)->profile_image,
            'user_email'=>optional($this->user)->email,
            'user_phone'=>optional($this->user)->mobile,
            'user_dob'=>optional($this->user)->date_of_birth,
            'user_gender'=>optional($this->user)->gender,
            'country_id' =>optional($this->user)->country,
            'state_id' => optional($this->user)->state,
            'city_id' =>optional($this->user)->city,
            'country_name' => optional(optional($this->user)->countries)->name,
            'state_name' => optional(optional($this->user)->states)->name,
            'city_name' => optional(optional($this->user)->cities)->name,
            'address' => optional($this->user)->address,
            'pincode' => optional($this->user)->pincode,
            'doctor_id'=> $this->doctor_id,
            'doctor_name'=>optional($this->doctor)->first_name .' '.optional($this->doctor)->last_name,
            'doctor_image'=>optional($this->doctor)->profile_image,
            'doctor_email'=>optional($this->doctor)->email,
            'doctor_phone'=>optional($this->doctor)->mobile,
            'clinic_id'=> $this->clinic_id,
            'clinic_name'=>optional($this->cliniccenter)->name,
            'clinic_image' => optional($this->cliniccenter)->file_url,
            'clinic_address' => optional($this->cliniccenter)->address,
            'clinic_phone' => optional($this->cliniccenter)->contact_number,
            'appointment_date'=> $this->appointment_date,
            'appointment_time'=> $this->appointment_time,
            'duration'=> $this->duration,
            'service_id'=> $this->service_id,
            'service_name'=> optional($this->clinicservice)->name,
            'service_type'=> optional($this->clinicservice)->type,
            'category_name' => optional(optional($this->clinicservice)->category)->name,
            'service_image'=> optional($this->clinicservice)->file_url,
            'service_type'=> optional($this->clinicservice)->type,
            'is_video_consultancy'=> optional($this->clinicservice)->is_video_consultancy,
            'appointment_extra_info'=> $this->appointment_extra_info,
            'total_amount'=> $totalAmount,
            'service_amount'=> $this->service_amount,
            'service_price'=> $this->service_price,
            'service_total' => $service_price ?? 0,
            'discount_type'=>optional($this->appointmenttransaction)->discount_type,
            'discount_value'=>optional($this->appointmenttransaction)->discount_value,
            'discount_amount'=>optional($this->appointmenttransaction)->discount_amount,
            'subtotal' => $subtotal,            
            'billing_final_discount_type' => $discount_type ?? '',
            'enable_final_billing_discount' => $enable_discount ?? 0,
            'billing_final_discount_value' => $total_discount ?? 0,
            'billing_final_discount_amount' => $billing_discount_total_amount ?? 0,
            'total_tax' => $total_tax,
            'tax_data'=>$tax_data,
            'billing_items' => BillingItemResource::collection($billingItems),
            'payment_status'=>optional($this->appointmenttransaction)->payment_status,
            'medical_report' => getAttachmentArray($this->getMedia('file_url'),null),
            'encounter_id' => optional($this->patientEncounter)->id,
            'encounter_description' => optional($this->patientEncounter)->description,
            'encounter_status' => optional($this->patientEncounter)->status,
            'tax' => json_decode(optional($this->appointmenttransaction)->tax_percentage),
            'reviews' => new DoctorReviewResource($this->serviceRating),
            'created_by'=> $this->created_by,
            'updated_by'=> $this->updated_by,
            'deleted_by'=> $this->deleted_by,
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at,
            'deleted_at'=> $this->deleted_at
        ];
    }
}
