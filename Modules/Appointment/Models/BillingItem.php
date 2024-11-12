<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\BillingItemFactory;
use Modules\Clinic\Models\ClinicsService;
use Modules\Appointment\Models\BillingRecord;

class BillingItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'billing_item';
    protected $fillable = ['billing_id','item_id','item_name','quantity','service_amount','total_amount','discount_type','discount_value'];
    protected $casts = [
        'billing_id' => 'integer',
        'item_id' => 'integer',
        'total_amount' => 'double',
        'service_amount' => 'double',
        'discount_value' => 'double',
        'quantity' => 'integer',
    ];
    protected static function newFactory(): BillingItemFactory
    {
        //return BillingItemFactory::new();
    }
    public function clinicservice()
    {
        return $this->belongsTo(ClinicsService::class, 'item_id','id');
    }
    public function billingRecord()
    {
        return $this->belongsTo(BillingRecord::class, 'billing_id', 'id');
    }
}
