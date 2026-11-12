@props(['compact' => false])

<!-- ======================================================== -->
<!-- E-WARGA 3-THEME ACCESSIBILITY SWITCHER (COLOR BLIND SAFE)-->
<!-- 1. Standar (Emerald)                                     -->
<!-- 2. Ramah Buta Warna (Biru - Amber Protanopia/Deuteranopia)-->
<!-- 3. Kontras Tinggi (Monokrom Tritanopia & Low Vision)     -->
<!-- ======================================================== -->
<div 
    x-data="{
        currentTheme: localStorage.getItem('ewarga_color_theme') || 'default',
        isOpen: false,
        themes: [
            {
                id: 'default',
                label: 'Standar (Emerald)',
                badge: 'Bawaan',
                desc: 'Hijau Zamrud & Cyan khas E-Warga',
                colorPill: 'bg-emerald-500',
                accentPill: 'bg-teal-400',
                icon: '🌿'
            },
            {
                id: 'colorblind',
                label: 'Ramah Buta Warna',
                badge: 'Protanopia / Deuteranopia',
                desc: 'Biru Safir & Amber Kuning (WCAG AAA)',
                colorPill: 'bg-blue-600',
                accentPill: 'bg-amber-400',
                icon: '👁️'
            },
            {
                id: 'contrast',
                label: 'Kontras Tinggi',
                badge: 'Tritanopia & Low Vision',
                desc: 'Monokrom pekat & garis batas tegas',
                colorPill: 'bg-black dark:bg-white',
                accentPill: 'bg-slate-400',
                icon: '🔲'
            }
        ],
        applyTheme(themeId) {
            this.currentTheme = themeId;
            localStorage.setItem('ewarga_color_theme', themeId);
            document.documentElement.classList.remove('theme-default', 'theme-colorblind', 'theme-contrast');
            document.documentElement.classList.add('theme-' + themeId);
            document.documentElement.setAttribute('data-color-theme', themeId);
            window.dispatchEvent(new CustomEvent('color-theme-changed', { detail: themeId }));
            this.isOpen = false;
        },
        getActiveLabel() {
            const found = this.themes.find(t => t.id === this.currentTheme);
            return found ? found.label : 'Standar';
        },
        getActiveIcon() {
            const found = this.themes.find(t => t.id === this.currentTheme);
            return found ? found.icon : '🌿';
        }
    }"
    class="relative inline-block text-left"
    @keydown.escape.window="isOpen = false"
>
    <!-- Trigger Button -->
    <button 
        type="button" 
        @click="isOpen = !isOpen" 
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
        :title="'Tema Aksesibilitas: ' + getActiveLabel()"
        aria-haspopup="true"
        :aria-expanded="isOpen"
    >
        <span x-text="getActiveIcon()" class="text-sm"></span>
        @if(!$compact)
            <span class="hidden md:inline text-[11px]" x-text="getActiveLabel()"></span>
        @endif
        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="isOpen" 
        x-cloak
        @click.outside="isOpen = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 mt-2 w-72 sm:w-80 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-2 z-50 focus:outline-none"
    >
        <!-- Header Dropdown -->
        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Pilih Tema & Aksesibilitas</span>
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Ramah Buta Warna & Low Vision</span>
        </div>

        <!-- Theme Options List -->
        <div class="py-1.5 space-y-1">
            <template x-for="theme in themes" :key="theme.id">
                <button 
                    type="button" 
                    @click="applyTheme(theme.id)"
                    class="w-full text-left p-2.5 rounded-xl transition flex items-start gap-3 cursor-pointer group"
                    :class="currentTheme === theme.id ? 'bg-slate-100 dark:bg-slate-800/90 font-black' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40'"
                >
                    <!-- Icon & Color Swatch -->
                    <div class="flex items-center gap-1 mt-0.5 shrink-0">
                        <span x-text="theme.icon" class="text-lg"></span>
                        <div class="flex -space-x-1 ml-1">
                            <span class="w-2.5 h-2.5 rounded-full ring-1 ring-white dark:ring-slate-900" :class="theme.colorPill"></span>
                            <span class="w-2.5 h-2.5 rounded-full ring-1 ring-white dark:ring-slate-900" :class="theme.accentPill"></span>
                        </div>
                    </div>

                    <!-- Theme Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="theme.label"></span>
                            <span 
                                class="text-[9px] px-1.5 py-0.5 rounded-md font-extrabold uppercase shrink-0"
                                :class="currentTheme === theme.id ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                                x-text="theme.badge"
                            ></span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-tight" x-text="theme.desc"></p>
                    </div>

                    <!-- Active Indicator Checkmark -->
                    <div class="shrink-0 mt-1" x-show="currentTheme === theme.id">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </button>
            </template>
        </div>

        <!-- Footer Info WCAG -->
        <div class="pt-2 pb-1 px-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10px] text-slate-400">
            <span>Standar Aksesibilitas WCAG 2.1 AAA</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400">3 Pilihan Tema</span>
        </div>
    </div>
</div>
