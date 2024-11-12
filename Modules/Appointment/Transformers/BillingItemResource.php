<?php

namespace Modules\Appointment\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Transformers\ServiceResource;

class BillingItemResource extends JsonResource
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
            'billing_id' => $this->billing_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,
            'quantity' => $this->quantity,
            'service_amount' => $this->service_amount,
            'total_amount' => $this->total_amount,
            'clinic_services' => new ServiceResource($this->clinicservice),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}