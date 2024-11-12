<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Database\factories\TemplateOtherDetailsFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class TemplateOtherDetails extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'template_other_details';
    
    protected $fillable = ['template_id','other_details'];
    
    protected static function newFactory(): TemplateOtherDetailsFactory
    {
        //return TemplateOtherDetailsFactory::new();
    }
}

