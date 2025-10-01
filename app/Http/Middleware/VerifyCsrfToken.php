<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs à exclure de la vérif CSRF (POST JSON API/SPA).
     * Tu peux laisser ça définitivement si tu veux éviter la friction SPA.
     */
    protected $except = [
        'v1/auth/*',
        'api/v1/auth/*',
    ];
}
