<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout session for subscription billing.
     *
     * Supports optional free trial via config('services.stripe.trial_days').
     */
    public function create(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent duplicate subscriptions
        if ($user->subscribed('default')) {
            return redirect()->route('billing.index')
                ->with('error', __('You already have an active subscription.'));
        }

        // Ensure user is a Stripe customer
        $user->createOrGetStripeCustomer();

        $priceId = config('services.stripe.basic_price_id');
        $trialDays = (int) config('services.stripe.trial_days', 0);

        $checkoutBuilder = $user->newSubscription('default', $priceId);

        // Add trial period if configured
        if ($trialDays > 0) {
            $checkoutBuilder->trialDays($trialDays);
        }

        $checkout = $checkoutBuilder->checkout([
            'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.cancel'),
        ]);

        return redirect($checkout->url);
    }
}
