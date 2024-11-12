<?php

namespace Modules\Appointment\Trait;
use Carbon\Carbon;
use Modules\Appointment\Models\BillingRecord;
use Modules\Appointment\Models\BillingItem;

trait BillingRecordTrait
{
    public function generateBillingRecord($encounter_details){

            $service_amount = optional($encounter_details->appointment)->service_amount;
            $tax_data = [];
            $taxes = json_decode(optional(optional($encounter_details->appointment)->appointmenttransaction)->tax_percentage);
            foreach($taxes as $tax){
                $amount = 0;
                if ($tax->type == 'percent') {
                    $amount = ($tax->value / 100) * $service_amount;
                }else {
                    $amount = $tax->value??0;
                }
                $tax_data[] = [
                    'title' => $tax->title,
                    'value' => $tax->value,
                    'type' => $tax->type,
                    'amount' => (float)number_format($amount, 2), 
                ];
            }

            $billing_record = [
                'encounter_id' => $encounter_details->id,
                'user_id' => $encounter_details->user_id,
                'clinic_id' => $encounter_details->clinic_id,
                'doctor_id' => $encounter_details->doctor_id,
                'service_id' => optional($encounter_details->appointment)->service_id,
                'total_amount' => optional($encounter_details->appointment)->total_amount ?? 0,
                'service_amount' => optional($encounter_details->appointment)->service_amount ?? 0,
                'discount_type' => optional(optional($encounter_details->appointment)->appointmenttransaction)->discount_type ?? null,
                'discount_value' => optional(optional($encounter_details->appointment)->appointmenttransaction)->discount_value ?? 0,
                'discount_amount' => optional(optional($encounter_details->appointment)->appointmenttransaction)->discount_amount ?? 0,
                'tax_data' => json_encode($tax_data), 
                'date' => date('Y-m-d', strtotime($encounter_details->encounter_date)),
                'payment_status' => optional(optional($encounter_details->appointment)->appointmenttransaction)->payment_status ?? 0
            ];

       
        
        $billingrecord=BillingRecord::create($billing_record);

        return  $billingrecord;


    }

    public function generateBillingItem($billing_record){
        $billing_item=[

            'billing_id' => $billing_record->id ?? null,
            'item_id' => $billing_record->service_id ?? null,
            'item_name' => optional($billing_record->clinicservice)->name ?? null,
            'quantity' => 1,
            'service_amount' => optional(optional($billing_record->patientencounter)->appointmentdetail)->service_price ?? 0,
            // 'total_amount' => $billing_record->service_amount  ?? null,
            'total_amount' => optional(optional($billing_record->patientencounter)->appointmentdetail)->service_amount  ?? null,
            'discount_type' => $billing_record->discount_type ?? null,
            'discount_value' => $billing_record->discount_value ?? 0,

        ];

        // $billing_item=BillingItem::create($billing_item);
        $billing_item = BillingItem::updateOrCreate(
            ['billing_id' => $billing_record->id,
            'item_id' => $billing_record->service_id,
            ],
            $billing_item
        );

        return  $billing_item;
    }
}
