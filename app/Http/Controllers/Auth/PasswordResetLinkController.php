<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    public function store(): JsonResponse
    {
        request()->validate(['email' => ['required','email']]);
        $status = Password::sendResetLink(request()->only('email'));
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }
        throw ValidationException::withMessages(['email' => [__($status)]]);
    }
}
