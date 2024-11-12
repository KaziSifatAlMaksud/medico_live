<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClinicsCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = request()->id;
        switch (strtolower($this->getMethod())) {
            case 'post':
                return [
                    'name' => 'unique:clinics_categories,name,'.$id,
                    'status' => 'boolean',
                ];
                break;
            case 'put':
            case 'patch':
                return [
                    // 'system_service_id' => 'required|integer',
                    'name' => 'unique:clinics_categories,name,'.$id,
                    'status' => 'boolean',
                ];
                break;
        }

        return [];
        
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => false,
            'message' => $validator->errors()->first(),
            'all_message' => $validator->errors(),
        ];

        if (request()->wantsJson() || request()->is('api/*')) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
    }
}
