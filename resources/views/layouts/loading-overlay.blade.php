{{-- Loading Overlay Partial --}}
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="text-center">
        {{-- Circular Loader --}}
        <div class="w-16 h-16 border-4 border-[var(--border)] border-t-white rounded-full animate-spin mx-auto mb-6"></div>
        
        {{-- Text --}}
        <div class="text-[var(--text-primary)] mb-2">Analyzing your tasks</div>
        <div class="text-sm text-[var(--text-secondary)]">Mapping goals, impact, and priorities.</div>
    </div>
</div>
