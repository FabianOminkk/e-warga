<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    /**
     * Balas pesan dari pengguna menggunakan Mesin Hybrid AI (Generatif Online + Offline Knowledge Base)
     */
    public function respond(string $message, ?User $user = null, array $conversationHistory = []): array
    {
        $messageClean = trim($message);
        if (empty($messageClean)) {
            return [
                'reply' => 'Halo! Ada yang bisa Bang Warga bantu terkait layanan lingkungan E-WARGA hari ini?',
                'mode' => 'smart_engine',
                'actions' => [],
                'suggestions' => $this->getDefaultSuggestions(),
            ];
        }

        $activeTenant = Tenant::getActiveTenant();
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');

        // Jika API Key tersedia, coba gunakan Google Gemini Generative API
        if (!empty($apiKey)) {
            $geminiResponse = $this->callGeminiApi($messageClean, $user, $activeTenant, $conversationHistory, $apiKey);
            if ($geminiResponse !== null) {
                return $geminiResponse;
            }
        }

        // Default & Fallback: Gunakan Otak Cerdas Knowledge Base Internal (100% Offline & Cepat)
        return $this->smartKnowledgeMatch($messageClean, $user, $activeTenant);
    }

    /**
     * Panggil Google Gemini API
     */
    protected function callGeminiApi(string $message, ?User $user, ?Tenant $tenant, array $history, string $apiKey): ?array
    {
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $userName = $user?->name ?? 'Warga';
        $userRole = $user?->role_label ?? 'Warga';
        $house = $user?->house?->block_and_number ?? 'Unit Warga';
        $tenantName = $tenant?->name ?? 'Cluster Melati Mas';
        $tenantType = $tenant?->environment_type ?? 'cluster';
        $canAccessGate = $user ? $user->canAccessGate() : false;
        $gateStatus = $canAccessGate ? 'Aktif Terverifikasi RW' : 'Terkunci / Belum Disetujui RW';

        $systemPrompt = <<<PROMPT
Anda adalah "Bang Warga AI", asisten kecerdasan buatan cerdas, ramah, dan solutif pada platform sistem lingkungan "E-WARGA Smart Community".
Karakter Anda:
- Gaya bicara santun, hangat, profesional, khas pengurus RT/RW Indonesia yang akrab namun menghormati warga.
- Sangat memahami seluruh fitur E-WARGA:
  1. Palang Pintu Otomatis IoT: RFID, QR Code Tamu/Kurir, dan tombol buka palang dari HP (khusus warga terverifikasi Ketua RW).
  2. Transparansi Kas Keuangan: Iuran bulanan, verifikasi transfer oleh Bendahara (Bpk. Joko), dan laporan real-time.
  3. Layanan Surat Pengantar RT Online: Pengajuan surat SKCK, domisili, nikah, dll., dengan QR code resmi.
  4. Keamanan & Pos Satpam: Pemantauan palang, tombol darurat (Panic Button), dan jadwal ronda siskamling malam.
  5. Posyandu Digital: Penimbangan balita, grafik tumbuh kembang standar WHO, deteksi stunting dini, dan posbindu lansia.
Konteks Pengguna Saat Ini:
- Nama: {$userName}
- Peran: {$userRole}
- Rumah: {$house}
- Lingkungan Aktif: {$tenantName} (Tipe: {$tenantType})
- Status Akses Pintu Gerbang: {$gateStatus}

Jawab dengan ringkas (maksimal 2-3 paragraf), jelas, dan berikan langkah konkret jika pengguna menanyakan panduan teknis.
PROMPT;

        try {
            $contents = [];
            // Tambahkan system prompt sebagai user/model pair jika model belum mendukung systemInstruction langsung di format dasar
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => "[SYSTEM INSTRUCTION]\n" . $systemPrompt]],
            ];
            $contents[] = [
                'role' => 'model',
                'parts' => [['text' => "Siap! Saya adalah Bang Warga AI, siap membantu Bpk/Ibu {$userName} di {$tenantName} dengan ramah dan akurat."]],
            ];

            // Tambahkan riwayat percakapan singkat (maksimal 4 interaksi terakhir)
            $recentHistory = array_slice($history, -4);
            foreach ($recentHistory as $item) {
                if (!empty($item['sender']) && !empty($item['text'])) {
                    $contents[] = [
                        'role' => ($item['sender'] === 'user') ? 'user' : 'model',
                        'parts' => [['text' => $item['text']]],
                    ];
                }
            }

            // Pesan saat ini
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]],
            ];

            $response = Http::timeout(6)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 600,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($replyText)) {
                    $matchedActions = $this->extractActionsFromKeywords($message);
                    return [
                        'reply' => $replyText,
                        'mode' => 'gemini_online',
                        'actions' => $matchedActions,
                        'suggestions' => $this->getDynamicSuggestions($message),
                    ];
                }
            } else {
                Log::warning('Gemini API call returned non-200: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Otak Cerdas Knowledge Base Internal E-WARGA (Offline / No-API Engine)
     */
    public function smartKnowledgeMatch(string $query, ?User $user, ?Tenant $tenant): array
    {
        $q = strtolower($query);
        $userName = $user?->name ?? 'Warga';
        $house = $user?->house?->block_and_number ?? 'Rumah Warga';
        $tenantName = $tenant?->name ?? 'Cluster Melati Mas';
        $canAccessGate = $user ? $user->canAccessGate() : true;

        // 1. GERBANG / PALANG PINTU / RFID / QR TAMU
        if (str_contains($q, 'gerbang') || str_contains($q, 'pintu') || str_contains($q, 'palang') || str_contains($q, 'portal') || str_contains($q, 'rfid') || str_contains($q, 'qr')) {
            if (!$canAccessGate) {
                return [
                    'reply' => "Halo Bpk/Ibu **{$userName}**! Akses palang gerbang pintar Anda saat ini masih **Terkunci** karena data KTP sedang dalam peninjauan Ketua RW. Setelah diverifikasi, Anda dapat membuka palang dari HP atau men-tap E-KTP pada scanner gerbang.",
                    'mode' => 'smart_engine',
                    'actions' => [
                        ['label' => 'Lihat Status Profil & KTP ➔', 'url' => route('warga.profile'), 'color' => 'amber'],
                        ['label' => 'Buka Portal Warga ➔', 'url' => route('warga.index'), 'color' => 'emerald'],
                    ],
                    'suggestions' => ['Kapan verifikasi RW disetujui?', 'Bagaimana cara buat QR tamu?', 'Cara hubungi satpam?'],
                ];
            }

            return [
                'reply' => "Untuk membuka palang gerbang pintar di **{$tenantName}**, Anda memiliki 3 cara praktis:\n\n1. **Aplikasi HP**: Tekan tombol hijau *'Buka Palang Gerbang'* pada menu Portal Warga.\n2. **Kartu RFID / E-KTP**: Cukup tap kartu fisik atau E-KTP Anda ke sensor RFID di tiang palang (motor servo langsung mengangkat palang 90°).\n3. **QR Code Tamu/Kurir**: Buat tiket tamu sementara di menu Warga untuk dibagikan ke kurir/tamu Anda via WhatsApp.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Buka Gerbang dari HP Sekarang ➔', 'url' => route('warga.index'), 'color' => 'emerald'],
                    ['label' => 'Buat Tiket QR Tamu / Kurir ➔', 'url' => route('warga.index'), 'color' => 'blue'],
                    ['label' => 'Coba Simulator Palang Virtual ➔', 'url' => route('simulator'), 'color' => 'slate'],
                ],
                'suggestions' => ['Berapa lama QR tamu berlaku?', 'Bagaimana jika kartu RFID hilang?', 'Cara bayar iuran bulanan?'],
            ];
        }

        // 2. IURAN BULANAN / KAS / REKENING / BAYAR
        if (str_contains($q, 'iuran') || str_contains($q, 'bayar') || str_contains($q, 'kas') || str_contains($q, 'tagihan') || str_contains($q, 'rekening') || str_contains($q, 'uang') || str_contains($q, 'keuangan')) {
            return [
                'reply' => "Informasi Keuangan Kas & Iuran Warga **{$tenantName}**:\n\n• Iuran pemeliharaan lingkungan (keamanan, kebersihan sampah, & penerangan) dibayarkan setiap awal bulan.\n• **Cara Pembayaran**: Masuk ke menu Portal Warga, pilih tagihan bulan aktif, lalu transfer ke Rekening Kas RT (**BCA: 8820-123-456 a.n Kas Guyub RT**).\n• Unggah bukti transfer di aplikasi; Bendahara RT (**Bpk. Joko**) akan memverifikasi dan saldo kas otomatis tercatat transparan.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Lihat Status Tagihan Iuran ➔', 'url' => route('warga.index') . '#iuran', 'color' => 'emerald'],
                    ['label' => 'Transparansi Kas di Dashboard ➔', 'url' => route('dashboard'), 'color' => 'blue'],
                ],
                'suggestions' => ['Apakah kas bisa dilihat seluruh warga?', 'Bagaimana jika lupa bayar?', 'Cara membuat surat pengantar?'],
            ];
        }

        // 3. SURAT PENGANTAR RT ONLINE
        if (str_contains($q, 'surat') || str_contains($q, 'pengantar') || str_contains($q, 'skck') || str_contains($q, 'domisili') || str_contains($q, 'nikah') || str_contains($q, 'ktp') || str_contains($q, 'kelurahan')) {
            return [
                'reply' => "Pengurusan **Surat Pengantar RT Online** di E-WARGA kini tanpa antre fisik:\n\n1. Buka menu **Layanan Surat RT**.\n2. Klik tombol **+ Ajukan Surat Baru**, pilih jenis surat (SKCK, Surat Domisili, Keterangan Belum Menikah, Usaha, atau Kematian).\n3. Masukkan keperluan dan lampirkan dokumen pendukung bila ada.\n4. Ketua RT akan menyetujui secara digital. Surat resmi ber-QR Code valid dapat Anda unduh dan cetak langsung!",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Buat Surat Pengantar Baru ➔', 'url' => route('letters.index'), 'color' => 'emerald'],
                    ['label' => 'Lihat Riwayat Pengajuan Surat ➔', 'url' => route('letters.index'), 'color' => 'slate'],
                ],
                'suggestions' => ['Berapa lama proses verifikasi surat?', 'Apakah QR code surat sah di kelurahan?', 'Bagaimana cara buka gerbang?'],
            ];
        }

        // 4. PANIK / DARURAT / MALING / KEBAKARAN / AMBULANS / SATPAM
        if (str_contains($q, 'darurat') || str_contains($q, 'panik') || str_contains($q, 'panic') || str_contains($q, 'maling') || str_contains($q, 'pencuri') || str_contains($q, 'kebakaran') || str_contains($q, 'ambulans') || str_contains($q, 'polisi') || str_contains($q, 'satpam') || str_contains($q, 'pos')) {
            return [
                'reply' => "🚨 **PROTOKOL TANGGAP DARURAT E-WARGA**:\n\n• **Tombol Panik Digital**: Tekan tombol merah *'DARURAT / PANIK'* di Portal Warga. Alarm di Pos Pantau Satpam (**Pak Slamet**) akan berbunyi seketika dan koordinat rumah Anda ({$house}) langsung ditandai di monitor satpam!\n• **Nomor Darurat Siaga**:\n  - Pos Satpam Lingkungan: Ext. 01 / 0812-9988-7711\n  - Polsek Terdekat: 110\n  - Pemadam Kebakaran (Damkar): 113\n  - Layanan Medis / Ambulans: 119",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => '🚨 Buka Tombol Darurat Satpam ➔', 'url' => route('warga.index'), 'color' => 'rose'],
                    ['label' => 'Hubungi Monitor Pos Satpam ➔', 'url' => route('satpam.console'), 'color' => 'slate'],
                ],
                'suggestions' => ['Apakah satpam standby 24 jam?', 'Bagaimana cara mengajukan jadwal ronda?', 'Cara bayar iuran?'],
            ];
        }

        // 5. JADWAL RONDA / SISKAMLING / KEAMANAN MALAM
        if (str_contains($q, 'ronda') || str_contains($q, 'siskamling') || str_contains($q, 'jaga') || str_contains($q, 'shift') || str_contains($q, 'tukar')) {
            return [
                'reply' => "Jadwal Keamanan & Siskamling di **{$tenantName}**:\n\n• Petugas Satpam berjaga 24 jam dalam 2 shift (Pagi 07:00-19:00 & Malam 19:00-07:00).\n• Ronda swakarsa warga dilaksanakan bergilir tiap malam pukul 21:00-04:00.\n• Anda dapat melihat daftar nama warga yang bertugas malam ini, melakukan absensi check-in digital, atau mengajukan tukar jadwal jika berhalangan hadir.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Lihat Jadwal Ronda & Satpam ➔', 'url' => route('schedules.index'), 'color' => 'emerald'],
                ],
                'suggestions' => ['Bagaimana cara tukar jadwal ronda?', 'Apakah ada denda jika tidak hadir ronda?', 'Cara buat surat pengantar?'],
            ];
        }

        // 6. POSYANDU / BALITA / STUNTING / IBU HAMIL / LANSIA
        if (str_contains($q, 'posyandu') || str_contains($q, 'stunting') || str_contains($q, 'balita') || str_contains($q, 'anak') || str_contains($q, 'timbang') || str_contains($q, 'imunisasi') || str_contains($q, 'lansia') || str_contains($q, 'hamil')) {
            return [
                'reply' => "Layanan **Posyandu Digital Terpadu**:\n\n• **Jadwal Kegiatan**: Setiap hari Sabtu pekan kedua tiap bulan di Balai Warga.\n• **Pelayanan**: Penimbangan berat badan, pengukuran tinggi badan, imunisasi balita, vitamin A, serta pemeriksaan tensi & gula darah lansia.\n• **Deteksi Dini Stunting**: Sistem otomatis memetakan grafik tumbuh kembang anak terhadap standar baku WHO Z-Score untuk mendeteksi gizi kurang sedini mungkin.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Buka Rekam Posyandu & Balita ➔', 'url' => route('posyandu.index'), 'color' => 'emerald'],
                ],
                'suggestions' => ['Kapan jadwal posyandu bulan ini?', 'Bagaimana cara mendaftarkan balita baru?', 'Cara bayar iuran kas?'],
            ];
        }

        // 7. VERIFIKASI IDENTITAS & KTP
        if (str_contains($q, 'verifikasi') || str_contains($q, 'ktp') || str_contains($q, 'acc') || str_contains($q, 'rw') || str_contains($q, 'profil') || str_contains($q, 'nik')) {
            return [
                'reply' => "Tentang **Verifikasi Akun & KTP Warga**:\n\n1. Sesuai standar keamanan terpadu lingkungan, setiap warga wajib melengkapi NIK, Nama Sesuai KTP, Nomor WhatsApp, dan foto KTP pada halaman Pengaturan Akun.\n2. Berkas KTP akan langsung ditinjau oleh Ketua RW (**Bpk. H. Rahmat Hidayat**).\n3. Segera setelah di-ACC oleh Pak RW, hak akses gerbang Anda aktif seketika dan data RFID Anda tersinkronisasi.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Lengkapi Data KTP di Profil ➔', 'url' => route('warga.profile'), 'color' => 'emerald'],
                ],
                'suggestions' => ['Berapa lama verifikasi RW?', 'Apakah data KTP aman?', 'Cara hubungi satpam?'],
            ];
        }

        // 8. SAMPAH / KEBERSIHAN
        if (str_contains($q, 'sampah') || str_contains($q, 'bersih') || str_contains($q, 'truk') || str_contains($q, 'got')) {
            return [
                'reply' => "Jadwal Pengangkutan Sampah & Kebersihan **{$tenantName}**:\n\n• **Pengambilan Sampah Rumah**: Setiap hari **Selasa, Kamis, dan Sabtu** pagi mulai pukul 07:30 WIB.\n• **Kerja Bakti Lingkungan**: Diselenggarakan setiap hari Minggu pertama di awal bulan.\n• Harap menempatkan kantong sampah tertutup di tempat sampah depan pagar rumah masing-masing sebelum truk pengangkut berkeliling.",
                'mode' => 'smart_engine',
                'actions' => [
                    ['label' => 'Lihat Transparansi Biaya Kebersihan ➔', 'url' => route('dashboard'), 'color' => 'emerald'],
                ],
                'suggestions' => ['Berapa iuran sampah bulanan?', 'Bagaimana melapor got tersumbat?', 'Cara buka gerbang?'],
            ];
        }

        // 9. SALAM / SAPAAN / DEFAULT GREETING
        return [
            'reply' => "Halo Bpk/Ibu **{$userName}**! Saya **Bang Warga AI**, asisten digital resmi lingkungan **{$tenantName}**.\n\nSaya siap memandu Anda kapan saja jika ada hal yang membingungkan seputar:\n• 🚪 **Palang Gerbang & RFID**: Cara scan kartu, buka palang dari HP, & tiket QR tamu.\n• 💳 **Iuran & Kas RT**: Cek tagihan bulanan & bukti transfer ke Bendahara.\n• 📜 **Surat Pengantar RT**: Pembuatan surat online resmi ber-QR Code.\n• 🚨 **Tombol Darurat**: Protokol respon pos satpam cepat tanggap.\n• 👶 **Posyandu**: Jadwal imunisasi balita & pemantauan gizi lansia.\n\nSilakan pilih topik cepat di bawah atau ketik langsung pertanyaan Anda!",
            'mode' => 'smart_engine',
            'actions' => [
                ['label' => 'Buka Portal Warga ➔', 'url' => route('warga.index'), 'color' => 'emerald'],
                ['label' => 'Layanan Surat RT ➔', 'url' => route('letters.index'), 'color' => 'blue'],
                ['label' => 'Jadwal Ronda Siskamling ➔', 'url' => route('schedules.index'), 'color' => 'slate'],
            ],
            'suggestions' => $this->getDefaultSuggestions(),
        ];
    }

    /**
     * Ekstrak action links dari pesan jika menggunakan Gemini Online
     */
    protected function extractActionsFromKeywords(string $message): array
    {
        $q = strtolower($message);
        $actions = [];

        if (str_contains($q, 'gerbang') || str_contains($q, 'pintu') || str_contains($q, 'rfid') || str_contains($q, 'tamu')) {
            $actions[] = ['label' => 'Buka Gerbang Warga ➔', 'url' => route('warga.index'), 'color' => 'emerald'];
        }
        if (str_contains($q, 'iuran') || str_contains($q, 'bayar') || str_contains($q, 'kas')) {
            $actions[] = ['label' => 'Status Tagihan Kas ➔', 'url' => route('warga.index') . '#iuran', 'color' => 'blue'];
        }
        if (str_contains($q, 'surat') || str_contains($q, 'pengantar') || str_contains($q, 'skck')) {
            $actions[] = ['label' => 'Buat Surat RT Online ➔', 'url' => route('letters.index'), 'color' => 'emerald'];
        }
        if (str_contains($q, 'darurat') || str_contains($q, 'panik') || str_contains($q, 'satpam')) {
            $actions[] = ['label' => '🚨 Tombol Darurat Satpam ➔', 'url' => route('warga.index'), 'color' => 'rose'];
        }
        if (str_contains($q, 'posyandu') || str_contains($q, 'balita') || str_contains($q, 'stunting')) {
            $actions[] = ['label' => 'Buka Posyandu Digital ➔', 'url' => route('posyandu.index'), 'color' => 'emerald'];
        }

        if (empty($actions)) {
            $actions[] = ['label' => 'Ke Portal Warga ➔', 'url' => route('warga.index'), 'color' => 'emerald'];
        }

        return $actions;
    }

    /**
     * Saran pertanyaan awal (Suggestions)
     */
    public function getDefaultSuggestions(): array
    {
        return [
            '🚪 Bagaimana cara membuka palang gerbang?',
            '💳 Bagaimana cara membayar iuran bulanan?',
            '📜 Bagaimana cara mengajukan surat pengantar?',
            '🚨 Bagaimana cara memanggil bantuan darurat satpam?',
            '👶 Kapan jadwal posyandu dan penimbangan balita?',
            '👮 Jam berapa jadwal ronda malam ini?',
        ];
    }

    /**
     * Dynamic suggestions berdasarkan kata kunci
     */
    protected function getDynamicSuggestions(string $message): array
    {
        $q = strtolower($message);
        if (str_contains($q, 'gerbang') || str_contains($q, 'pintu')) {
            return ['Cara buat tiket QR untuk tamu?', 'Apakah E-KTP bisa untuk tap gerbang?', 'Cara bayar iuran RT?'];
        }
        if (str_contains($q, 'iuran') || str_contains($q, 'bayar')) {
            return ['Berapa saldo kas RT saat ini?', 'Cara konfirmasi bukti bayar?', 'Cara mengajukan surat RT?'];
        }
        if (str_contains($q, 'surat')) {
            return ['Berapa lama surat disetujui?', 'Apakah surat bisa dicetak mandiri?', 'Cara buka palang gerbang?'];
        }

        return $this->getDefaultSuggestions();
    }
}
