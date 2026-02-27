<?php

namespace App\Http\Controllers\Billing;

use App\Models\User;
use App\Notifications\TrialEndingSoonNotification;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle invoice.paid → grant/confirm access.
     */
    public function handleInvoicePaid(array $payload): Response
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;

        if ($stripeCustomerId) {
            Log::info('Stripe: Invoice paid', [
                'customer' => $stripeCustomerId,
                'invoice_id' => $payload['data']['object']['id'] ?? null,
                'amount_paid' => $payload['data']['object']['amount_paid'] ?? null,
            ]);
        }

        // Cashier handles subscription status sync automatically
        return $this->successMethod();
    }

    /**
     * Handle invoice.payment_failed → alert user / revoke access.
     */
    public function handleInvoicePaymentFailed(array $payload): Response
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;

        if ($stripeCustomerId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();

            Log::warning('Stripe: Invoice payment failed', [
                'customer' => $stripeCustomerId,
                'invoice_id' => $payload['data']['object']['id'] ?? null,
                'user_id' => $user?->id,
            ]);

            // TODO: Send payment failed notification to user
            // $user?->notify(new PaymentFailedNotification());
        }

        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.trial_will_end → notify user.
     */
    public function handleCustomerSubscriptionTrialWillEnd(array $payload): Response
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;

        if ($stripeCustomerId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();

            if ($user) {
                $trialEnd = $payload['data']['object']['trial_end'] ?? null;

                Log::info('Stripe: Trial ending soon', [
                    'user_id' => $user->id,
                    'trial_end' => $trialEnd,
                ]);

                $user->notify(new TrialEndingSoonNotification(
                    trialEndsAt: $trialEnd ? \Carbon\Carbon::createFromTimestamp($trialEnd) : null
                ));
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.deleted → revoke access.
     */
    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        Log::info('Stripe: Subscription deleted', [
            'customer' => $payload['data']['object']['customer'] ?? null,
            'subscription_id' => $payload['data']['object']['id'] ?? null,
        ]);

        // Cashier handles the status update automatically
        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    /**
     * Handle customer.subscription.updated → sync status changes.
     */
    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        Log::info('Stripe: Subscription updated', [
            'customer' => $payload['data']['object']['customer'] ?? null,
            'status' => $payload['data']['object']['status'] ?? null,
        ]);

        return parent::handleCustomerSubscriptionUpdated($payload);
    }
}
