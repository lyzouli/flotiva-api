<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'account_name' => ['required','string','max:150'],
            'name'         => ['required','string','max:120'],
            'email'        => ['required','email','max:255','unique:users,email'],
            'password'     => ['required','string','min:8','confirmed'],
        ];
    }
}
