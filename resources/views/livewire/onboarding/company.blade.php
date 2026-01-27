<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Validate};
use App\Models\Company;

new
    #[Layout('components.layouts.app')]
    #[Title('Company Information')]
    class extends Component {
    #[Validate('nullable|url')]
    public string $domain = '';

    #[Validate('required|min:2')]
    public string $name = '';

    #[Validate('required')]
    public string $businessModel = '';

    #[Validate('required')]
    public string $stage = '';

    #[Validate('required')]
    public string $teamSize = '';

    #[Validate('required')]
    public string $timeframe = '';

    public string $additionalInfo = '';

    public bool $fetched = false;
    public bool $loading = false;

    public function mount(): void
    {
        $company = auth()->user()->company;

        if ($company) {
            $this->name = $company->name ?? '';
            $this->businessModel = $company->business_model ?? '';
            $this->stage = $company->stage ?? '';
            $this->teamSize = $company->team_size ?? '';
            $this->timeframe = $company->timeframe ?? 'This week';
            $this->additionalInfo = $company->additional_info ?? '';
        }
    }

    public function fetchDomain(): void
    {
        if (empty($this->domain))
            return;

        $this->loading = true;

        // Extract company name from domain
        $domainName = preg_replace('/\.(com|io|net|org|co|de|app)$/', '', $this->domain);
        $this->name = ucfirst($domainName);

        $this->fetched = true;
        $this->loading = false;
    }

    public function save(): void
    {
        $this->validate();

        $company = auth()->user()->company;

        if (!$company) {
            $company = new Company();
            $company->owner_user_id = auth()->id();
        }

        $company->name = $this->name;
        $company->business_model = $this->businessModel;
        $company->stage = $this->stage;
        $company->team_size = $this->teamSize;
        $company->timeframe = $this->timeframe;
        $company->additional_info = $this->additionalInfo;
        $company->save();

        $this->redirect(route('app.goals'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-medium text-[var(--text-primary)] mb-2">Company Information</h1>
        <p class="text-[var(--text-secondary)]">Give Axia your business context.</p>
    </div>

    <!-- Main Card -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)]">
        <form wire:submit="save" class="space-y-8">
            <!-- Domain Fetch Section -->
            <div class="space-y-4">
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <flux:input wire:model="domain" :label="__('Domain')" placeholder="acme.com" icon="globe-alt" />
                    </div>
                    <flux:button wire:click="fetchDomain" variant="outline">
                        Fetch Company Data
                    </flux:button>
                </div>
                @if($fetched)
                    <flux:text class="text-green-500">✓ Data found. Review below.</flux:text>
                @endif
            </div>

            <div class="h-px bg-[var(--border-color)]"></div>

            <!-- Manual Inputs -->
            <div class="space-y-6">
                <flux:input wire:model="name" :label="__('Company Name')" placeholder="Enter company name" required />

                <flux:select wire:model="businessModel" :label="__('Business Model')"
                    placeholder="Select business model">
                    <flux:select.option value="SaaS">SaaS</flux:select.option>
                    <flux:select.option value="Marketplace">Marketplace</flux:select.option>
                    <flux:select.option value="E-Commerce">E-Commerce</flux:select.option>
                    <flux:select.option value="Services">Services</flux:select.option>
                    <flux:select.option value="Other">Other</flux:select.option>
                </flux:select>

                <flux:select wire:model="stage" :label="__('Stage')" placeholder="Select stage">
                    <flux:select.option value="Pre-PMF">Pre-PMF</flux:select.option>
                    <flux:select.option value="PMF">PMF</flux:select.option>
                    <flux:select.option value="Scale">Scale</flux:select.option>
                </flux:select>

                <flux:input wire:model="teamSize" :label="__('Team Size')" placeholder="e.g. 5-10" required />

                <flux:select wire:model="timeframe" :label="__('Timeframe')" placeholder="Select timeframe">
                    <flux:select.option value="This week">This week</flux:select.option>
                    <flux:select.option value="This month">This month</flux:select.option>
                    <flux:select.option value="This quarter">This quarter</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="additionalInfo" :label="__('Additional Information')"
                    placeholder="Any additional context..." rows="4" />
            </div>

            <!-- Next Button -->
            <div class="flex justify-end pt-4">
                <flux:button type="submit" variant="primary">
                    Continue to Goals
                </flux:button>
            </div>
        </form>
    </div>
</div>