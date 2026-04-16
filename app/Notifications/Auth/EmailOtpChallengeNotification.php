<?php

namespace App\Notifications\Auth;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpChallengeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $code,
        protected CarbonInterface $expiresAt,
        protected ?string $destinationMasked = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Codigo OTP para ingresar al sistema')
            ->greeting('Codigo de verificacion')
            ->line('Usa este codigo OTP para completar el acceso al sistema.')
            ->line("Codigo: {$this->code}")
            ->line('Este codigo expira en '.max(1, now()->diffInMinutes($this->expiresAt)).' minuto(s).')
            ->line($this->destinationMasked ? "Destino registrado: {$this->destinationMasked}" : 'No compartas este codigo con nadie.');
    }
}
