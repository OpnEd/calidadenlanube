<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'identification' => 'required|string|max:255|unique:customers,identification',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phonenumber' => 'required|string|max:20',
            'data' => 'nullable|json',
        ];
    }
}
