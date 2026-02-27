<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Redirect the user to the Stripe Customer Portal.
     *
     * Allows self-service management of subscriptions, payment methods,
     * cancellation, and resumption.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $user = $request->user();

        return $user->redirectToBillingPortal(route('billing.index'));
    }
}
