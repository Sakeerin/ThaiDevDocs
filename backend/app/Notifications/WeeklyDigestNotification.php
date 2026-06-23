<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class WeeklyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Collection $articles
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');

        $message = (new MailMessage)
            ->subject('สรุปประจำสัปดาห์ — Thai Developer Docs')
            ->greeting('สวัสดี ' . $notifiable->name)
            ->line('บทความใหม่และน่าสนใจจากสัปดาห์นี้:');

        foreach ($this->articles as $article) {
            $message->line('- ' . $article->title . ' — ' . $frontendUrl . '/docs/' . $article->slug);
        }

        return $message
            ->action('อ่านเอกสาร', $frontendUrl)
            ->line('คุณได้รับอีเมลนี้เพราะเปิดใช้งาน Weekly Digest ในการตั้งค่า');
    }
}
