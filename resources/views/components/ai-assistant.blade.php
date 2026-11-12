{{-- 
    KOMPONEN ANDROID-STYLE IN-APP AI ASSISTANT ("BANG WARGA AI")
    Menggunakan Arsitektur Hybrid:
    - Mode 1: Offline Smart Knowledge Engine (100% Berfungsi Tanpa Biaya / API Key)
    - Mode 2: Online Generative AI (Google Gemini API via GEMINI_API_KEY)
--}}

<div 
    x-data="{
        isOpen: false,
        isLoading: false,
        userInput: '',
        suggestions: [
            '🚪 Bagaimana cara membuka palang gerbang?',
            '💳 Bagaimana cara membayar iuran bulanan?',
            '📜 Bagaimana cara mengajukan surat pengantar?',
            '🚨 Bagaimana cara memanggil bantuan darurat satpam?',
            '👶 Kapan jadwal posyandu balita?',
            '👮 Jam berapa jadwal ronda malam ini?'
        ],
        messages: [
            {
                sender: 'bot',
                text: 'Halo! Saya **Bang Warga AI**, asisten digital resmi lingkungan Anda. Ada yang membingungkan atau butuh bantuan seputar iuran, palang gerbang RFID, surat RT, atau keamanan?',
                actions: [
                    { label: 'Buka Gerbang Warga ➔', url: '{{ route('warga.index') }}', color: 'emerald' },
                    { label: 'Layanan Surat RT ➔', url: '{{ route('letters.index') }}', color: 'blue' }
                ],
                time: '{{ now()->format('H:i') }}'
            }
        ],
        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    if (this.$refs.aiInput) this.$refs.aiInput.focus();
                });
            }
        },
        openWithQuery(q) {
            this.isOpen = true;
            this.userInput = q;
            this.sendMessage();
        },
        scrollToBottom() {
            if (this.$refs.chatContainer) {
                this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
            }
        },
        async sendMessage() {
            const query = this.userInput.trim();
            if (!query || this.isLoading) return;

            const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            // Tambahkan pesan user ke stream
            this.messages.push({
                sender: 'user',
                text: query,
                time: nowTime
            });

            this.userInput = '';
            this.isLoading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                
                // Siapkan riwayat singkat percakapan
                const historyPayload = this.messages.slice(-6).map(m => ({
                    sender: m.sender,
                    text: m.text
                }));

                const response = await fetch('{{ route('ai.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        message: query,
                        history: historyPayload
                    })
                });

                const json = await response.json();
                
                if (json.success && json.data) {
                    this.messages.push({
                        sender: 'bot',
                        text: json.data.reply,
                        actions: json.data.actions || [],
                        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                    });
                    if (json.data.suggestions && json.data.suggestions.length > 0) {
                        this.suggestions = json.data.suggestions;
                    }
                } else {
                    this.messages.push({
                        sender: 'bot',
                        text: 'Maaf, terjadi kendala saat memproses permintaan Anda. Silakan coba lagi atau pilih topik bantuan cepat di bawah.',
                        actions: [],
                        time: nowTime
                    });
                }
            } catch (err) {
                console.error('AI Chat Error:', err);
                this.messages.push({
                    sender: 'bot',
                    text: 'Koneksi ke server terputus sejenak. Namun seluruh sistem mandiri E-WARGA tetap dapat Anda akses melalui menu di bawah.',
                    actions: [
                        { label: 'Portal Warga ➔', url: '{{ route('warga.index') }}', color: 'emerald' },
                        { label: 'Surat Pengantar ➔', url: '{{ route('letters.index') }}', color: 'blue' }
                    ],
                    time: nowTime
                });
            } finally {
                this.isLoading = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    if (this.$refs.aiInput) this.$refs.aiInput.focus();
                });
            }
        },
        clearChat() {
            this.messages = [
                {
                    sender: 'bot',
                    text: 'Obrolan telah dibersihkan. Halo kembali! Apa yang bisa Bang Warga bantu untuk Anda hari ini?',
                    actions: [],
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                }
            ];
            this.$nextTick(() => this.scrollToBottom());
        },
        renderMarkdown(raw) {
            if (!raw) return '';
            // Basic secure markdown renderer (bold, italic, list, newlines)
            let escaped = raw
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            
            // Bold **text**
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class=\'font-bold text-slate-900 dark:text-white\'>$1</strong>');
            // Italic *text*
            escaped = escaped.replace(/\*(.*?)\*/g, '<em class=\'italic\'>$1</em>');
            // Newlines
            escaped = escaped.replace(/\n\n/g, '<br><br>');
            escaped = escaped.replace(/\n/g, '<br>');
            
            return escaped;
        }
    }"
    x-on:open-ai-chat.window="toggle()"
    x-on:ask-ai.window="openWithQuery($event.detail.query)"
    class="relative"
>

    <!-- ======================================================== -->
    <!-- 1. ANDROID FLOATING ACTION BUTTON (FAB) DOCK             -->
    <!-- ======================================================== -->
    <div class="fixed bottom-20 lg:bottom-6 right-4 sm:right-6 z-40">
        <button 
            type="button" 
            @click="toggle()" 
            class="group relative flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white font-black text-xs shadow-xl shadow-emerald-600/30 hover:shadow-emerald-600/50 hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer border border-white/20 touch-press card-interactive btn-shimmer"
            aria-label="Tanya Asisten AI Bang Warga"
            :title="isOpen ? 'Tutup Asisten Bang Warga AI' : 'Buka Asisten Bang Warga AI'"
        >
            <!-- Animated Pulse Ring Indicator -->
            <span class="relative flex h-3.5 w-3.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-300 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-cyan-100 text-[10px] items-center justify-center">✨</span>
            </span>

            <span class="text-base inline-block animate-bounce-subtle">🤖</span>
            <span class="tracking-wide">Tanya Bang Warga</span>
            
            <span class="hidden sm:inline-block px-1.5 py-0.5 rounded-full bg-white/20 text-[9px] font-extrabold uppercase tracking-wider backdrop-blur-xs">
                AI RT
            </span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- 2. ANDROID BOTTOM SHEET / MODAL DRAWER CHAT PANEL       -->
    <!-- ======================================================== -->
    <div 
        x-show="isOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-end sm:justify-end p-0 sm:p-6 transition-all duration-300"
    >
        <!-- Backdrop Blur (Tap outside to close) -->
        <div 
            x-show="isOpen" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-200" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            @click="isOpen = false" 
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"
        ></div>

        <!-- Android Bottom Sheet Container (Bottom-anchored on mobile, floating card on desktop) -->
        <div 
            x-show="isOpen" 
            x-transition:enter="ease-out duration-300 transform" 
            x-transition:enter-start="translate-y-full sm:translate-y-8 sm:opacity-0" 
            x-transition:enter-end="translate-y-0 sm:opacity-100" 
            x-transition:leave="ease-in duration-200 transform" 
            x-transition:leave-start="translate-y-0 sm:opacity-100" 
            x-transition:leave-end="translate-y-full sm:translate-y-8 sm:opacity-0" 
            class="relative w-full sm:max-w-md h-[88vh] sm:h-[640px] max-h-[88vh] bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 flex flex-col overflow-hidden z-10"
        >
            
            <!-- Android Drag Handle (Mobile Ergonomics) -->
            <div class="sm:hidden w-full flex justify-center pt-2.5 pb-1 cursor-grab">
                <div class="w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>

            <!-- Header App Bar (Android Material 3 Style) -->
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/90 backdrop-blur-md flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="relative w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                        🤖
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-white dark:border-slate-900 rounded-full animate-pulse"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white leading-none">Bang Warga AI</h3>
                            <span class="px-1.5 py-0.2 bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 text-[9px] font-black rounded-full uppercase">
                                Siap Bantu
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                            <span>Asisten Pintar E-WARGA</span>
                            <span>•</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">Online</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <!-- Reset / Clear Chat -->
                    <button 
                        @click="clearChat()" 
                        type="button" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer text-xs" 
                        title="Bersihkan Percakapan"
                    >
                        🔄
                    </button>
                    <!-- Close Button -->
                    <button 
                        @click="isOpen = false" 
                        type="button" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer text-base font-bold"
                        aria-label="Tutup"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- Suggestions Carousel (Pill Chips) -->
            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/80 shrink-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>💡 Pertanyaan Populer Warga:</span>
                </div>
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                    <template x-for="(sug, index) in suggestions" :key="index">
                        <button 
                            @click="openWithQuery(sug)" 
                            type="button" 
                            class="whitespace-nowrap px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 hover:text-emerald-700 dark:hover:text-emerald-300 text-[11px] font-semibold transition border border-slate-200/80 dark:border-slate-700 cursor-pointer touch-press shrink-0"
                            x-text="sug"
                        ></button>
                    </template>
                </div>
            </div>

            <!-- Conversation Stream Body -->
            <div 
                x-ref="chatContainer" 
                class="flex-1 p-4 space-y-4 overflow-y-auto custom-sidebar-scroll bg-slate-50/50 dark:bg-slate-950/40"
            >
                <template x-for="(msg, i) in messages" :key="i">
                    <div>
                        <!-- Bot Message Bubble -->
                        <template x-if="msg.sender === 'bot'">
                            <div class="flex items-start gap-2.5 max-w-[92%] animate-in fade-in slide-in-from-bottom-2 duration-200">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5 shadow-xs">
                                    🤖
                                </div>
                                <div class="space-y-2">
                                    <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-3.5 rounded-2xl rounded-tl-xs shadow-xs border border-slate-200/80 dark:border-slate-700 text-xs leading-relaxed space-y-2">
                                        <div x-html="renderMarkdown(msg.text)"></div>
                                        
                                        <!-- Action Buttons Inside Bot Bubble -->
                                        <template x-if="msg.actions && msg.actions.length > 0">
                                            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/80 flex flex-wrap gap-1.5">
                                                <template x-for="(act, ai) in msg.actions" :key="ai">
                                                    <a 
                                                        :href="act.url" 
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-black text-[11px] transition shadow-xs touch-press"
                                                        :class="{
                                                            'bg-emerald-600 hover:bg-emerald-700 text-white': act.color === 'emerald',
                                                            'bg-blue-600 hover:bg-blue-700 text-white': act.color === 'blue',
                                                            'bg-rose-600 hover:bg-rose-700 text-white': act.color === 'rose',
                                                            'bg-amber-500 hover:bg-amber-600 text-slate-950': act.color === 'amber',
                                                            'bg-slate-800 hover:bg-slate-700 text-white dark:bg-slate-700 dark:hover:bg-slate-600': !act.color || act.color === 'slate'
                                                        }"
                                                        x-text="act.label"
                                                    ></a>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="text-[9px] text-slate-400 px-1 font-mono" x-text="msg.time"></div>
                                </div>
                            </div>
                        </template>

                        <!-- User Message Bubble -->
                        <template x-if="msg.sender === 'user'">
                            <div class="flex items-start justify-end gap-2.5 max-w-[90%] ml-auto animate-in fade-in slide-in-from-bottom-2 duration-200">
                                <div class="space-y-1 text-right">
                                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-3.5 rounded-2xl rounded-tr-xs shadow-xs text-xs leading-relaxed font-medium">
                                        <div x-text="msg.text"></div>
                                    </div>
                                    <div class="text-[9px] text-slate-400 px-1 font-mono" x-text="msg.time"></div>
                                </div>
                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs shrink-0 mt-0.5 font-bold">
                                    👤
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Loading Typing Indicator -->
                <div x-show="isLoading" x-cloak class="flex items-center gap-2 max-w-[85%] text-slate-500 dark:text-slate-400">
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0">
                        🤖
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-xs border border-slate-200/80 dark:border-slate-700 flex items-center gap-1.5 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce [animation-delay:0.4s]"></span>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 ml-1.5">Bang Warga sedang mengetik...</span>
                    </div>
                </div>
            </div>

            <!-- Footer Android Pill Input Bar -->
            <div class="p-3 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input 
                            x-ref="aiInput" 
                            x-model="userInput" 
                            type="text" 
                            placeholder="Tanya apa saja seputar E-WARGA..." 
                            class="w-full px-4 py-3 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs font-medium border border-transparent focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-900 focus:outline-hidden transition shadow-inner"
                            :disabled="isLoading"
                            maxlength="500"
                        />
                    </div>
                    
                    <button 
                        type="submit" 
                        class="p-3 rounded-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white transition flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/30 cursor-pointer touch-press"
                        :disabled="!userInput.trim() || isLoading"
                        title="Kirim Pertanyaan"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <div class="flex items-center justify-between text-[9px] text-slate-400 px-3 pt-2">
                    <span>💡 <em>Didukung Hybrid AI (Offline Smart Knowledge & Generative Engine)</em></span>
                    <span>v2.5 Android Edition</span>
                </div>
            </div>

        </div>
    </div>
</div>

