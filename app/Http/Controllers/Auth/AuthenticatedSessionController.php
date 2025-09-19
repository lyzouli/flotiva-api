<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        // Validation manuelle pour répondre en JSON (évite redirection)
        $email = (string) $request->input('email');
        $password = (string) $request->input('password');

        if ($email === '' || $password === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid credentials format.',
                'errors'  => [
                    'email'    => ['The email field must be a valid email.'],
                    'password' => ['The password field is required.'],
                ]
            ], 422);
        }

        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
            // Mauvais identifiants -> 422 JSON (pas 302)
            return response()->json([
                'message' => 'Authentication failed.',
                'errors'  => ['email' => [__('auth.failed')]],
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json(['ok' => true], 200);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true], 200);
    }
}
