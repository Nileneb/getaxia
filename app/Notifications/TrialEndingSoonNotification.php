<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?Carbon $trialEndsAt = null
    ) {
        $this->onQueue('stripe-webhooks');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = $this->trialEndsAt
            ? (int) now()->diffInDays($this->trialEndsAt)
            : 3;

        return (new MailMessage)
            ->subject(__('Your Axia trial ends in :days days', ['days' => $daysLeft]))
            ->greeting(__('Hi :name,', ['name' => $notifiable->first_name ?? 'there']))
            ->line(__('Your free trial of Axia will end in :days days.', ['days' => $daysLeft]))
            ->line(__('After your trial ends, your subscription will begin and your payment method will be charged.'))
            ->action(__('Manage Subscription'), route('billing.index'))
            ->line(__('If you have any questions, feel free to reach out to our support team.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'trial_ends_at' => $this->trialEndsAt?->toIso8601String(),
        ];
    }
}
