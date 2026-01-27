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
                <label class="block text-sm text-[var(--text-primary)]">Domain</label>
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <flux:icon.globe-alt
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-secondary)]" />
                        <input type="text" wire:model="domain" placeholder="acme.com"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg pl-10 pr-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50" />
                    </div>
                    <button type="button" wire:click="fetchDomain" wire:loading.attr="disabled"
                        wire:target="fetchDomain"
                        class="px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border-color)] rounded-lg text-[var(--text-primary)] transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="fetchDomain">Fetch Company Data</span>
                        <span wire:loading wire:target="fetchDomain">Fetching...</span>
                    </button>
                </div>
                @if($fetched)
                    <p class="text-sm text-[var(--accent-green)]">✓ Data found. Review below.</p>
                @endif
            </div>

            <div class="h-px bg-[var(--border-color)]"></div>

            <!-- Manual Inputs -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Company Name</label>
                    <input type="text" wire:model="name" placeholder="Enter company name"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50" />
                    @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Business Model</label>
                    <select wire:model="businessModel"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50">
                        <option value="">Select business model</option>
                        <option value="SaaS">SaaS</option>
                        <option value="Marketplace">Marketplace</option>
                        <option value="E-Commerce">E-Commerce</option>
                        <option value="Services">Services</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('businessModel') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Stage</label>
                    <select wire:model="stage"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50">
                        <option value="">Select stage</option>
                        <option value="Pre-PMF">Pre-PMF</option>
                        <option value="PMF">PMF</option>
                        <option value="Scale">Scale</option>
                    </select>
                    @error('stage') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Team Size</label>
                    <input type="text" wire:model="teamSize" placeholder="e.g. 5-10"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50" />
                    @error('teamSize') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Timeframe</label>
                    <select wire:model="timeframe"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50">
                        <option value="">Select timeframe</option>
                        <option value="This week">This week</option>
                        <option value="This month">This month</option>
                        <option value="This quarter">This quarter</option>
                    </select>
                    @error('timeframe') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Additional Information</label>
                    <textarea wire:model="additionalInfo" placeholder="Any additional context..." rows="4"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"></textarea>
                </div>
            </div>

            <!-- Next Button -->
            <div class="flex justify-end pt-4">
                <button type="submit" wire:loading.attr="disabled"
                    class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Continue to Goals</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>