<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ยืนยันอีเมลของคุณ — Thai Developer Docs')
            ->greeting('สวัสดี ' . $notifiable->name)
            ->line('กรุณาคลิกปุ่มด้านล่างเพื่อยืนยันอีเมลของคุณ')
            ->action('ยืนยันอีเมล', $this->verificationUrl($notifiable))
            ->line('หากคุณไม่ได้สมัครสมาชิก ไม่จำเป็นต้องดำเนินการใดๆ');
    }
}
