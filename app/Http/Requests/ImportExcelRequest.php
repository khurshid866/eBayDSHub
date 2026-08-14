<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // Max 10MB
            'mode' => ['required', 'string', 'in:create,update'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select an Excel or CSV file to import.',
            'file.mimes' => 'The uploaded file must be a valid .xlsx, .xls, or .csv file.',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }
}
