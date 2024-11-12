<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\TemplatePrescriptionFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class TemplatePrescription extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'template_prescription';
    
    protected $fillable = ['template_id','name','frequency','duration','instruction'];
    
    protected static function newFactory(): TemplatePrescriptionFactory
    {
        //return TemplatePrescriptionFactory::new();
    }
}
