<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as Base;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailApi extends Base
{
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

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);
        return (new MailMessage)
            ->subject('Vérifiez votre adresse e-mail')
            ->line('Merci d’avoir créé un compte Flotiva.')
            ->action('Vérifier mon e-mail', $url)
            ->line('Ce lien expire dans 60 minutes.');
    }
}
