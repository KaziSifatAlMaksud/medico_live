<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClinicRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = request()->id;
        return [
             'name' => 'required|unique:clinic,name,'.$id,
             'email' => 'required|string|unique:clinic,email',
            // 'address' => 'required|string',
            // 'pincode' => 'required',
            // 'contact_number' => 'required|string',
            // 'email' => 'required|email',


        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
