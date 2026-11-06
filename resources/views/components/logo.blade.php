@props([
    'size' => 'md',
    'showText' => true,
    'href' => null
])

@php
    $dimensions = match($size) {
        'sm' => ['icon' => 'w-7 h-7', 'title' => 'text-sm', 'sub' => 'text-[9px]'],
        'lg' => ['icon' => 'w-11 h-11', 'title' => 'text-xl', 'sub' => 'text-[11px]'],
        default => ['icon' => 'w-9 h-9', 'title' => 'text-base', 'sub' => 'text-[10px]'],
    };
@endphp

<{{ $href ? 'a' : 'div' }} 
    @if($href) href="{{ $href }}" @endif 
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 group select-none']) }}
>
    <!-- SVG Vector Emblem (Zero Background Artifacts in Dark/Light) -->
    <div class="{{ $dimensions['icon'] }} shrink-0 relative flex items-center justify-center">
        <svg class="w-full h-full transform group-hover:scale-105 transition-transform duration-200" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="logoGrad_{{ $size }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#10B981"/>
                    <stop offset="50%" stop-color="#059669"/>
                    <stop offset="100%" stop-color="#047857"/>
                </linearGradient>
                <linearGradient id="logoAccent_{{ $size }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#34D399"/>
                    <stop offset="100%" stop-color="#38BDF8"/>
                </linearGradient>
            </defs>
            <rect width="48" height="48" rx="14" fill="url(#logoGrad_{{ $size }})"/>
            <rect x="1" y="1" width="46" height="46" rx="13" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
            <circle cx="24" cy="11" r="2.2" fill="#FFFFFF"/>
            <path d="M18 14 C21 11.5, 27 11.5, 30 14" stroke="#A7F3D0" stroke-width="2" stroke-linecap="round"/>
            <path d="M11 25 L24 15 L37 25" stroke="#FFFFFF" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24.5 V35.5 C15 36.5, 16 37, 17 37 H31 C32 37, 33 36.5, 33 35.5 V24.5" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="17.5" y="27.5" width="13" height="4" rx="2" fill="url(#logoAccent_{{ $size }})"/>
        </svg>
    </div>

    @if($showText)
        <div class="leading-tight">
            <div class="{{ $dimensions['title'] }} font-extrabold tracking-tight text-slate-900 dark:text-white transition-colors duration-200">
                E-WARGA
            </div>
            <div class="{{ $dimensions['sub'] }} font-black tracking-wider uppercase text-emerald-600 dark:text-emerald-400">
                Smart Community OS
            </div>
        </div>
    @endif
</{{ $href ? 'a' : 'div' }}>
