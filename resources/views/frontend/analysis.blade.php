@extends('layouts.frontend')

@section('title', 'Analysis')

@section('content')
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
        <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] rounded-es-lg rounded-ee-lg lg:rounded-ss-lg lg:rounded-ee-none">
            <h1 class="mb-1 font-medium">Analysis</h1>
            <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">This Blade page represents the AnalysisPage React component.</p>
            <div class="mt-4">
                {{-- Place analysis-specific markup here --}}
            </div>
        </div>

        <aside class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ms-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-e-lg! aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
            <div class="p-6">Illustration / side panel</div>
        </aside>
    </main>
@endsection
