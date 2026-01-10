{{-- Company Page --}}
<div class="max-w-3xl mx-auto px-6 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">About Your Company</h1>
        <p>Tell us about your business so Axia can give you personalized insights.</p>
    </div>

    {{-- Main Card --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 space-y-6">
        {{-- Company Name --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Company Name</label>
            <input
                type="text"
                x-model="companyData.name"
                placeholder="Enter your company name"
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 transition-colors"
            />
        </div>

        {{-- Business Model --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Business Model</label>
            <input
                type="text"
                x-model="companyData.businessModel"
                placeholder="e.g., SaaS, Marketplace, E-commerce"
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 transition-colors"
            />
        </div>

        {{-- Stage --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Stage</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="stage in ['Pre-seed', 'Seed', 'PMF', 'Series A', 'Series B+']" :key="stage">
                    <button
                        @click="companyData.stage = stage"
                        :class="companyData.stage === stage 
                            ? 'bg-[#E94B8C]/10 border-[#E94B8C]/50 text-[#E94B8C]' 
                            : 'bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--text-secondary)]'"
                        class="px-4 py-2 rounded-lg border transition-colors text-sm"
                        x-text="stage"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Team Size --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Team Size</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="size in ['1-3', '4-7', '8-12', '13-25', '25+']" :key="size">
                    <button
                        @click="companyData.teamSize = size"
                        :class="companyData.teamSize === size 
                            ? 'bg-[#E94B8C]/10 border-[#E94B8C]/50 text-[#E94B8C]' 
                            : 'bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--text-secondary)]'"
                        class="px-4 py-2 rounded-lg border transition-colors text-sm"
                        x-text="size"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Timeframe --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Analysis Timeframe</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="time in ['This week', 'This month', 'This quarter']" :key="time">
                    <button
                        @click="companyData.timeframe = time"
                        :class="companyData.timeframe === time 
                            ? 'bg-[#E94B8C]/10 border-[#E94B8C]/50 text-[#E94B8C]' 
                            : 'bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--text-secondary)]'"
                        class="px-4 py-2 rounded-lg border transition-colors text-sm"
                        x-text="time"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Additional Info --}}
        <div>
            <label class="block text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide">Additional Context (Optional)</label>
            <textarea
                x-model="companyData.additionalInfo"
                placeholder="Any extra context about your business..."
                rows="3"
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none transition-colors"
            ></textarea>
        </div>
    </div>

    {{-- Next Button --}}
    <div class="flex justify-end mt-8">
        <button
            @click="setStep('goals')"
            class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
        >
            Continue to Goals
        </button>
    </div>
</div>
