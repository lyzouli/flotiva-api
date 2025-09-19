<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'email'    => ['required','email'],
            'password' => ['required','string'],
            'device'   => ['nullable','string','max:120'], // nom du token PAT
        ];
    }
}
