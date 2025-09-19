<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterAccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class RegisteredUserController extends Controller
{
    public function __construct(private RegisterAccountService $service) {}

    public function store(RegisterRequest $request): JsonResponse
    {
        [$account, $user] = $this->service->handle(
            $request->string('account_name'),
            $request->string('name'),
            $request->string('email'),
            $request->string('password')
        );

        event(new Registered($user)); // envoie l’email de vérif

        // Token pratique pour mobile/tests (PAT)
        $token = $user->createToken('bootstrap')->plainTextToken;

        return response()->json([
            'message' => 'Account created. Please verify your email.',
            'token'   => $token,
            'user'    => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => (bool) $user->email_verified_at,
            ],
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'slug' => $account->slug,
            ],
        ], 201);
    }
}
