@extends('layouts.axia')

@section('content')
<div x-data="axiaApp()" class="flex h-screen w-full bg-[var(--bg-primary)]">

    {{-- Chat Panel --}}
    @include('axia.partials.chat-panel')

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col">
        {{-- Step Bar --}}
        @include('axia.partials.step-bar')

        {{-- Content Area --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Company Page --}}
            <template x-if="currentStep === 'company' && currentPage === 'main'">
                @include('axia.pages.company')
            </template>

            {{-- Goals Page --}}
            <template x-if="currentStep === 'goals' && currentPage === 'main'">
                @include('axia.pages.goals')
            </template>

            {{-- To-Dos Page --}}
            <template x-if="currentStep === 'todos' && currentPage === 'main'">
                @include('axia.pages.todos')
            </template>

            {{-- Analysis Page --}}
            <template x-if="currentStep === 'analysis' && currentPage === 'main'">
                @include('axia.pages.analysis')
            </template>

            {{-- Profile Page --}}
            <template x-if="currentPage === 'profile'">
                @include('axia.pages.profile')
            </template>

            {{-- Settings Page --}}
            <template x-if="currentPage === 'settings'">
                @include('axia.pages.settings')
            </template>

            {{-- Past Analyses Page --}}
            <template x-if="currentPage === 'past-analyses'">
                @include('axia.pages.past-analyses')
            </template>

            {{-- How It Works Page --}}
            <template x-if="currentPage === 'how-it-works'">
                @include('axia.pages.how-it-works')
            </template>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <template x-if="isLoading">
        @include('axia.partials.loading-overlay')
    </template>
</div>

@push('scripts')
<script>
function axiaApp() {
    return {
        // Navigation State
        currentStep: 'analysis',
        currentPage: 'main',
        isLoading: false,
        darkMode: true,

        // User Data
        userProfile: {
            name: '{{ auth()->user()->name ?? "John Founder" }}',
            email: '{{ auth()->user()->email ?? "john@acmecorp.com" }}',
            company: 'Acme Corp',
        },

        // Company Data
        companyData: {
            name: 'Acme Corp',
            businessModel: 'SaaS',
            stage: 'PMF',
            teamSize: '8-12',
            timeframe: 'This week',
            additionalInfo: 'B2B productivity tool for remote teams',
        },

        // Goals
        goals: [
            { id: '1', title: 'Launch new enterprise pricing tier', description: 'Create and launch enterprise plan to target larger customers', priority: 'high' },
            { id: '2', title: 'Improve onboarding completion rate', description: 'Increase from 45% to 65%', priority: 'high' },
            { id: '3', title: 'Build referral program', description: 'Incentivize existing users to bring new customers', priority: 'mid' },
        ],

        // To-Dos
        todos: [
            { id: '1', text: 'Finalize enterprise pricing model' },
            { id: '2', text: 'Create sales deck for enterprise customers' },
            { id: '3', text: 'Update website with new pricing tier' },
            { id: '4', text: 'Fix onboarding step 3 bug' },
            { id: '5', text: 'Add progress indicators to onboarding' },
            { id: '6', text: 'Write blog post about new feature' },
            { id: '7', text: 'Update team wiki documentation' },
            { id: '8', text: 'Review analytics dashboard' },
            { id: '9', text: 'Schedule customer interviews' },
            { id: '10', text: 'Organize team offsite' },
            { id: '11', text: 'Update LinkedIn company page' },
            { id: '12', text: 'Research referral program tools' },
        ],

        // Chat Messages
        messages: [
            { id: '1', text: 'Welcome to Axia. Let me help you prioritize what matters.', sender: 'ai' },
        ],

        // Past Analyses
        pastAnalyses: [
            { id: '1', date: 'November 20, 2025', score: 55, tasksAnalyzed: 12, goals: 3 },
        ],

        // Focus Score
        focusScore: 55,

        // Expanded task cards
        expandedTasks: [],

        // Methods
        init() {
            // Initialize from server data if available
            @if(isset($companyData))
            this.companyData = @json($companyData);
            @endif
            @if(isset($goals))
            this.goals = @json($goals);
            @endif
            @if(isset($todos))
            this.todos = @json($todos);
            @endif
        },

        setStep(step) {
            this.currentStep = step;
        },

        setPage(page) {
            this.currentPage = page;
        },

        toggleTheme() {
            this.darkMode = !this.darkMode;
        },

        addGoal() {
            this.goals.push({
                id: Date.now().toString(),
                title: '',
                description: '',
                priority: 'mid',
            });
        },

        updateGoal(id, field, value) {
            const goal = this.goals.find(g => g.id === id);
            if (goal) goal[field] = value;
        },

        deleteGoal(id) {
            this.goals = this.goals.filter(g => g.id !== id);
        },

        addTodo(text) {
            if (!text.trim()) return;
            this.todos.push({
                id: Date.now().toString(),
                text: text.trim(),
            });
        },

        addBulkTodos(text) {
            const lines = text.split('\n').map(l => l.trim()).filter(l => l);
            lines.forEach(line => {
                this.todos.push({
                    id: Date.now().toString() + Math.random(),
                    text: line,
                });
            });
        },

        updateTodo(id, text) {
            const todo = this.todos.find(t => t.id === id);
            if (todo) todo.text = text;
        },

        deleteTodo(id) {
            this.todos = this.todos.filter(t => t.id !== id);
        },

        sendMessage(text) {
            if (!text.trim()) return;
            this.messages.push({ id: Date.now().toString(), text, sender: 'user' });
            
            // Simulate AI response
            setTimeout(() => {
                this.messages.push({
                    id: (Date.now() + 1).toString(),
                    text: 'I understand. Let me help you with that.',
                    sender: 'ai',
                });
            }, 500);
        },

        toggleTaskExpand(id) {
            const idx = this.expandedTasks.indexOf(id);
            if (idx > -1) {
                this.expandedTasks.splice(idx, 1);
            } else {
                this.expandedTasks.push(id);
            }
        },

        isTaskExpanded(id) {
            return this.expandedTasks.includes(id);
        },

        analyzeTask(todo) {
            const text = todo.text.toLowerCase();
            
            if (text.includes('enterprise') || text.includes('pricing') || text.includes('sales')) {
                return { impact: 'high', score: 92, summary: 'Strong contribution to your top goal and short-term revenue path.', reasoning: ['Directly supports "Launch enterprise pricing" goal', 'Revenue-driving with immediate impact', 'Founder-level leverage task'], relatedGoal: 'Launch enterprise pricing', impactRating: 'Revenue-driving', delegationFit: 'Founder-led' };
            } else if (text.includes('onboarding') || text.includes('bug') || text.includes('fix')) {
                return { impact: 'high', score: 88, summary: 'Critical for user retention and directly supports completion rate goal.', reasoning: ['Blocks users from completing onboarding', 'Supports "Improve onboarding completion" goal', 'High urgency this week'], relatedGoal: 'Improve onboarding', impactRating: 'Retention-critical', delegationFit: 'Technical lead' };
            } else if (text.includes('customer') || text.includes('interview') || text.includes('analytics')) {
                return { impact: 'mid', score: 65, summary: 'Provides strategic insights but not immediately revenue-generating.', reasoning: ['Valuable for product roadmap decisions', 'Supports long-term strategic goals', 'Can be scheduled flexibly'], relatedGoal: 'Research & insights', impactRating: 'Strategic input', delegationFit: 'PM or founder' };
            } else if (text.includes('referral') || text.includes('research')) {
                return { impact: 'mid', score: 58, summary: 'Preparatory work for future growth, lower urgency this week.', reasoning: ['Supports "Build referral program" goal', 'Mid-term growth initiative', 'Not time-sensitive'], relatedGoal: 'Build referral program', impactRating: 'Future growth', delegationFit: 'Growth team' };
            } else {
                return { impact: 'low', score: 32, summary: 'Limited connection to current high-priority goals.', reasoning: ['No direct alignment with top goals', 'Can be postponed or delegated', 'Low urgency within timeframe'], relatedGoal: 'Operational', impactRating: 'Low urgency', delegationFit: 'Delegate' };
            }
        },

        get analyzedTasks() {
            return this.todos.filter(t => t.text.trim()).map(todo => ({
                ...todo,
                ...this.analyzeTask(todo),
            }));
        },

        get highTasks() {
            return this.analyzedTasks.filter(t => t.impact === 'high');
        },

        get midTasks() {
            return this.analyzedTasks.filter(t => t.impact === 'mid');
        },

        get lowTasks() {
            return this.analyzedTasks.filter(t => t.impact === 'low');
        },

        get highImpactGoals() {
            return this.goals.filter(g => g.priority === 'high').slice(0, 2);
        },

        getImpactColor(impact) {
            if (impact === 'high') return '#4CAF50';
            if (impact === 'mid') return '#FFB74D';
            return '#FF8A65';
        },

        getScoreColor(score) {
            if (score >= 70) return '#4CAF50';
            if (score >= 50) return '#FFB74D';
            return '#FF8A65';
        },

        getPriorityLabel(impact) {
            if (impact === 'high') return 'High';
            if (impact === 'mid') return 'Mid';
            return 'Low';
        },

        handleNewWeek() {
            this.goals = [];
            this.todos = [];
            this.currentStep = 'company';
        },

        logout() {
            document.getElementById('logout-form').submit();
        },

        async saveData() {
            this.isLoading = true;
            try {
                await fetch('{{ route("axia.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        companyData: this.companyData,
                        goals: this.goals,
                        todos: this.todos,
                    }),
                });
            } catch (error) {
                console.error('Failed to save:', error);
            } finally {
                this.isLoading = false;
            }
        },
    };
}
</script>
@endpush
@endsection
