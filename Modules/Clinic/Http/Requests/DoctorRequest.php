<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'mobile' => 'required|string',
            'commission_id' => 'required',
            'clinic_id' => 'required',
            'service_id' => 'required',


            // Define other validation rules for your fields
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
