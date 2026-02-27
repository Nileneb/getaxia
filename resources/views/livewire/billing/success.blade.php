<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<x-layouts.app title="{{ __('Subscription Confirmed') }}">
    <flux:main class="space-y-6">
        <div class="max-w-lg mx-auto text-center space-y-6 py-12">
            <div class="flex justify-center">
                <flux:icon name="check-circle" class="size-16 text-green-500" />
            </div>

            <flux:heading size="xl">{{ __('Welcome to Axia Pro!') }}</flux:heading>

            <flux:text>
                {{ __('Your subscription is now active. You have full access to all AI-powered focus coaching features.') }}
            </flux:text>

            <div class="flex justify-center gap-3">
                <flux:button variant="primary" href="{{ route('dashboard') }}" wire:navigate>
                    {{ __('Go to Dashboard') }}
                </flux:button>
                <flux:button variant="ghost" href="{{ route('billing.index') }}" wire:navigate>
                    {{ __('View Subscription') }}
                </flux:button>
            </div>
        </div>
    </flux:main>
</x-layouts.app>
