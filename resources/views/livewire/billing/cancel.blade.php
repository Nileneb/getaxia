<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
    #[Layout('components.layouts.app')]
    #[Title('Checkout Cancelled')]
    class extends Component {
    //
}; ?>

<div>
    <flux:main class="space-y-6">
        <div class="max-w-lg mx-auto text-center space-y-6 py-12">
            <div class="flex justify-center">
                <flux:icon name="x-circle" class="size-16 text-zinc-400" />
            </div>

            <flux:heading size="xl">{{ __('Checkout Cancelled') }}</flux:heading>

            <flux:text>
                {{ __('No worries — you can subscribe anytime. Your data is safe and waiting for you.') }}
            </flux:text>

            <div class="flex justify-center gap-3">
                <flux:button variant="primary" href="{{ route('billing.index') }}" wire:navigate>
                    {{ __('Back to Billing') }}
                </flux:button>
                <flux:button variant="ghost" href="{{ route('dashboard') }}" wire:navigate>
                    {{ __('Go to Dashboard') }}
                </flux:button>
            </div>
        </div>
    </flux:main>
</div>
