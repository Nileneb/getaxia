@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center ' . $class]) }}>
    <span class="text-white font-medium">A</span>
</div>
