<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmailVerificationNotificationController extends Controller
{
    public function store(): JsonResponse
    {
        if (request()->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified.']);
        }
        request()->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent.']);
    }
}
