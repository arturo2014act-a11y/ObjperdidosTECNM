<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Notificación de verificación de correo con enlace de expiración configurable (RF-68, RF-69, RF-70).
 *
 * La expiración se controla mediante auth.verification.expire (en minutos),
 * que a su vez lee la variable de entorno VERIFICATION_EXPIRE del archivo .env.
 */
class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Genera la URL firmada y temporal de verificación.
     * Sobreescribe el método del padre para añadir caducidad configurable.
     */
    protected function verificationUrl($notifiable): string
    {
        $expireMinutes = (int) Config::get('auth.verification.expire', 60);

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expireMinutes),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}

