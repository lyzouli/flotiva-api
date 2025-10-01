<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as Base;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailApi extends Base
{
    protected string $frontendPath = '/auth/verify-email';

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    protected function frontendVerificationUrl($notifiable): string
    {
        $signedUrl = $this->verificationUrl($notifiable);
        $query = parse_url($signedUrl, PHP_URL_QUERY) ?: '';
        $frontendBase = rtrim(config('app.frontend_url'), '/');
        $frontendUrl = $frontendBase . $this->frontendPath;

        return $query ? $frontendUrl . '?' . $query : $frontendUrl;
    }

    public function toMail($notifiable)
    {
        $url = $this->frontendVerificationUrl($notifiable);
        return (new MailMessage)
            ->subject('Vérifiez votre adresse e-mail')
            ->line('Merci d’avoir créé un compte Flotiva.')
            ->action('Vérifier mon e-mail', $url)
            ->line('Ce lien expire dans 60 minutes.');
    }
}
