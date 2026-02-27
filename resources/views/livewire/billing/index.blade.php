<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.app')]
    #[Title('Billing')]
    class extends Component {
    public bool $isSubscribed = false;
    public bool $onTrial = false;
    public bool $isCancelled = false;
    public ?string $subscriptionStatus = null;
    public ?string $trialEndsAt = null;
    public ?string $endsAt = null;
    public ?string $currentPlan = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->isSubscribed = $user->subscribed('default');
        $this->onTrial = $user->onTrial('default');

        if ($this->isSubscribed) {
            $subscription = $user->subscription('default');
            $this->subscriptionStatus = $subscription->stripe_status;
            $this->isCancelled = $subscription->cancelled();
            $this->endsAt = $subscription->ends_at?->format('d.m.Y');
            $this->trialEndsAt = $subscription->trial_ends_at?->format('d.m.Y');
            $this->currentPlan = $subscription->stripe_price;
        }
    }
}; ?>

<div>
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Billing & Subscription') }}</flux:heading>
                <flux:subheading>{{ __('Manage your subscription plan and payment methods.') }}</flux:subheading>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <flux:callout variant="danger">
                <flux:callout.heading>{{ __('Error') }}</flux:callout.heading>
                {{ session('error') }}
            </flux:callout>
        @endif

        @if(session('warning'))
            <flux:callout variant="warning">
                <flux:callout.heading>{{ __('Notice') }}</flux:callout.heading>
                {{ session('warning') }}
            </flux:callout>
        @endif

        @if(session('success'))
            <flux:callout variant="success">
                <flux:callout.heading>{{ __('Success') }}</flux:callout.heading>
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Current Plan Card --}}
            <div
                class="space-y-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Current Plan') }}</flux:heading>

                @if($isSubscribed)
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            @if($onTrial)
                                <flux:badge variant="warning">{{ __('Trial') }}</flux:badge>
                            @elseif($isCancelled)
                                <flux:badge variant="danger">{{ __('Cancelled') }}</flux:badge>
                            @else
                                <flux:badge variant="success">{{ __('Active') }}</flux:badge>
                            @endif
                        </div>

                        @if($onTrial && $trialEndsAt)
                            <flux:text>
                                {{ __('Your trial ends on :date.', ['date' => $trialEndsAt]) }}
                            </flux:text>
                        @endif

                        @if($isCancelled && $endsAt)
                            <flux:text>
                                {{ __('Your subscription will end on :date. You can resume before then.', ['date' => $endsAt]) }}
                            </flux:text>
                        @endif

                        <div class="flex gap-3 pt-2">
                            <form method="POST" action="{{ route('billing.portal') }}">
                                @csrf
                                <flux:button variant="primary" href="{{ route('billing.portal') }}">
                                    {{ __('Manage Billing') }}
                                </flux:button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        <flux:text>{{ __('You don\'t have an active subscription yet.') }}</flux:text>
                        <flux:text class="text-sm text-zinc-500">
                            {{ __('Subscribe to unlock the full power of Axia — AI-driven focus coaching for founders.') }}
                        </flux:text>
                    </div>
                @endif
            </div>

            {{-- Subscribe Card (only shown when not subscribed) --}}
            @unless($isSubscribed)
                <div
                    class="space-y-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg">{{ __('Axia Pro') }}</flux:heading>

                    <div class="space-y-3">
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <flux:icon name="check-circle" variant="mini" class="text-green-500" />
                                {{ __('Unlimited AI-powered todo analysis') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <flux:icon name="check-circle" variant="mini" class="text-green-500" />
                                {{ __('Goal & KPI tracking') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <flux:icon name="check-circle" variant="mini" class="text-green-500" />
                                {{ __('Missing todo detection') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <flux:icon name="check-circle" variant="mini" class="text-green-500" />
                                {{ __('Priority scoring & delegation suggestions') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <flux:icon name="check-circle" variant="mini" class="text-green-500" />
                                {{ __('AI chat coaching') }}
                            </li>
                        </ul>

                        @if(config('services.stripe.trial_days') > 0)
                            <flux:text class="text-sm font-medium text-blue-600">
                                {{ __(':days days free trial included!', ['days' => config('services.stripe.trial_days')]) }}
                            </flux:text>
                        @endif

                        <form method="POST" action="{{ route('billing.checkout') }}">
                            @csrf
                            <flux:button variant="primary" type="submit" class="w-full">
                                @if(config('services.stripe.trial_days') > 0)
                                    {{ __('Start Free Trial') }}
                                @else
                                    {{ __('Subscribe Now') }}
                                @endif
                            </flux:button>
                        </form>
                    </div>
                </div>
            @endunless
        </div>
    </flux:main>
</div>
