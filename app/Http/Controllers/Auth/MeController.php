<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // La relation accounts peut aussi lancer une exception si modèle/table manquent.
        // On la protège pour éviter un 500.
        $accounts = [];
        try {
            $accounts = $user->accounts()->get(['accounts.id','name','slug']);
        } catch (\Throwable $e) {
            // logguer si tu veux: \Log::warning('accounts() failed: '.$e->getMessage());
            $accounts = [];
        }

        return response()->json([
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'accounts'          => $accounts,
        ], 200);
    }
}
