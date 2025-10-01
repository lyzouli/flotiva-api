<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\RegisterAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class SocialGoogleController extends Controller
{
    public function __construct(private RegisterAccountService $registrar)
    {
    }

    public function redirect(Request $request): RedirectResponse
    {
        $redirectUrl = $request->string('redirect')->toString();

        if ($redirectUrl !== '') {
            $this->ensureRedirectAllowed($redirectUrl);
            $request->session()->put('oauth.redirect', $redirectUrl);
        }

        $driver = Socialite::driver('google')
            ->scopes(config('services.google.scopes', ['openid', 'profile', 'email']));

        if ($prompt = config('services.google.prompt')) {
            $driver->with(['prompt' => $prompt]);
        }

        return $driver->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless(false)->user();
        } catch (\Throwable $exception) {
            return $this->redirectWithError($request);
        }

        $user = $this->resolveUser($googleUser);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return Redirect::to($this->pullRedirectTarget($request));
    }

    private function resolveUser(SocialUser $googleUser): User
    {
        $providerId = $googleUser->getId();
        $email = $googleUser->getEmail();

        $user = User::query()
            ->where('provider_name', 'google')
            ->where('provider_id', $providerId)
            ->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            $user->forceFill([
                'provider_name' => 'google',
                'provider_id' => $providerId,
                'provider_metadata' => $this->buildMetadata($googleUser),
            ]);

            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }

            $user->save();

            return $user;
        }

        if (! $email) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Google account does not expose an email address.');
        }

        $name = $googleUser->getName()
            ?? ($googleUser->user['given_name'] ?? null)
            ?? (str_contains($email, '@') ? Str::before($email, '@') : null)
            ?? 'Utilisateur Google';

        $accountName = sprintf('Compte de %s', $name);

        [$account, $user] = $this->registrar->handle(
            $accountName,
            $name,
            $email,
            Str::password(32)
        );

        $user->forceFill([
            'provider_name' => 'google',
            'provider_id' => $providerId,
            'provider_metadata' => $this->buildMetadata($googleUser),
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function buildMetadata(SocialUser $googleUser): array
    {
        return array_filter([
            'avatar' => $googleUser->getAvatar(),
            'email' => $googleUser->getEmail(),
        ]);
    }

    private function pullRedirectTarget(Request $request): string
    {
        $target = $request->session()->pull('oauth.redirect');

        if (! $target) {
            return $this->frontendBase() . '/auth/callback';
        }

        return $target;
    }

    private function redirectWithError(Request $request): RedirectResponse
    {
        $target = $this->pullRedirectTarget($request);
        $separator = str_contains($target, '?') ? '&' : '?';

        return Redirect::to($target . $separator . 'error=social');
    }

    private function ensureRedirectAllowed(string $redirect): void
    {
        $allowed = collect($this->allowedRedirectHosts());
        $targetHost = parse_url($redirect, PHP_URL_HOST);

        if (! $targetHost || ! $allowed->contains($targetHost)) {
            abort(Response::HTTP_FORBIDDEN, 'Redirect host is not allowed.');
        }
    }

    private function allowedRedirectHosts(): array
    {
        $configured = $this->frontendBase();
        $extra = env('FRONTEND_ADDITIONAL_URLS');

        $urls = array_filter(array_map('trim', array_filter([
            $configured,
            ...($extra ? explode(',', $extra) : []),
        ])));

        return array_values(array_unique(array_map(
            static fn ($url) => parse_url($url, PHP_URL_HOST) ?: $url,
            $urls,
        )));
    }

    private function frontendBase(): string
    {
        $frontend = rtrim((string) config('app.frontend_url'), '/');

        if ($frontend === '') {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'FRONTEND_URL is not configured.');
        }

        return $frontend;
    }
}
