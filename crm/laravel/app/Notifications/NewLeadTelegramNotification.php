<?php

namespace App\Notifications;

use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadTelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * @param array $leadData Данные о лиде
     * @param int $topicId ID топика (message_thread_id)
     * @param int|string|null $managerTelegramId Telegram User ID менеджера (для HTML-тега)
     */
    public function __construct(
        public array $leadData,
        public int $topicId,
        public int|string|null $managerTelegramId = null)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    /**
     * 
     */
   public function toTelegram($notifiable): TelegramMessage
    {
        // 1. Формируем кликабельное упоминание менеджера
        if ($this->managerTelegramId) {
            $managerMention = "<a href=\"tg://user?id={$this->managerTelegramId}\">" . 
                               e($this->leadData['manager_name'] ?? 'Менеджер') . 
                               "</a>";
        } elseif (!empty($this->leadData['manager_username'])) {
            $managerMention = "@" . ltrim($this->leadData['manager_username'], '@');
        } else {
            $managerMention = "<i>Не назначен</i>";
        }

        // 2. ID группы берем из конфига или из notifiable-объекта
        $chatId = config('services.telegram.group_id');

        $telegramOptions = ['parse_mode' => 'HTML'];
        if ($this->topicId > 0) {
            $telegramOptions['message_thread_id'] = $this->topicId;
        }

        return TelegramMessage::create()
            ->to($chatId)                                 // ID супергруппы (-100...)
            ->options($telegramOptions)                   // Включаем HTML разметку и, при необходимости, ID топика
            ->line("🔥 <b>Новый лид в CRM!</b>")
            ->line("")
            ->line("👤 <b>Клиент:</b> " . e($this->leadData['name']))
            ->line("📞 <b>Телефон:</b> " . e($this->leadData['phone']))
            ->line("💰 <b>Сумма:</b> " . e($this->leadData['amount']) . " €")
            ->line("📝 <b>Услуга:</b> " . e($this->leadData['service_name'] ?? 'Общая консультация'))
            ->line("")
            ->line("👨‍💼 <b>Ответственный:</b> {$managerMention}")
            ->button('Открыть в CRM', $this->leadData['crm_link'] ?? 'https://...-crm.com');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
