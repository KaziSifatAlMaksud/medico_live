<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\TemplateMedicalHistoryFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class TemplateMedicalHistory extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'template_medical_history';
    
    protected $fillable = ['template_id','type','title','is_from_template'];

    
    protected static function newFactory(): TemplateMedicalHistoryFactory
    {
        //return TemplateMedicalHistoryFactory::new();
    }
}
