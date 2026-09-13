<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Telegram\TelegramChannel;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Customer|Order|Payment $model
    ) {
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return match (true) {
            $this->model instanceof Customer => $this->customerNotification(),
            $this->model instanceof Order => $this->orderNotification(),
            $this->model instanceof Payment => $this->paymentNotification(),
        };
    }

    private function customerNotification(): TelegramMessage
    {
        return TelegramMessage::create()
            ->to(config('services.telegram.chat_id'))
            ->messageThreadId(config('services.telegram.topics.customer'))
            ->content(
                "🆕 *New Customer*\n\n" .
                "ID: `{$this->model->id}`\n" .
                "Name: {$this->model->name}\n" .
                "Phone: {$this->model->phone}\n" .
                ($this->model->email
                    ? "Email: {$this->model->email}\n"
                    : '')
            );
    }

    private function orderNotification(): TelegramMessage
    {
        $executor = $this->model->assignments
            ->first()?->executor;

        $message =
            "🛒 *New Order*\n\n" .
            "Order: `#{$this->model->id}`\n" .
            "Customer: {$this->model->customer->name}\n" .
            "Total: {$this->model->total_amount}\n" .
            "Status: {$this->model->status}\n";

        if ($executor) {
            $message .= "\n👤 Executor: " .
                $this->mentionUser($executor);
        }

        return TelegramMessage::create()
            ->to(config('services.telegram.chat_id'))
            ->messageThreadId(config('services.telegram.topics.order'))
            ->content($message);
    }

    private function paymentNotification(): TelegramMessage
    {
        $executor = $this->model->order
            ->assignments
            ->first()?->executor;

        $message =
            "💳 *Payment received*\n\n" .
            "Payment: `#{$this->model->id}`\n" .
            "Order: `#{$this->model->order_id}`\n" .
            "Amount: {$this->model->amount}\n" .
            "Method: {$this->model->method}\n";

        if ($executor) {
            $message .= "\n👤 Executor: " .
                $this->mentionUser($executor);
        }

        return TelegramMessage::create()
            ->to(config('services.telegram.chat_id'))
            ->messageThreadId(config('services.telegram.topics.payment'))
            ->content($message);
    }

    private function mentionUser(User $user): string
    {
        return $user->telegram_username;
           
    }
}