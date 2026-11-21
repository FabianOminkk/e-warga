import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Komponen Animasi Angka Bertambah Halus (Count-Up Animation with Ease-Out)
Alpine.data('countUp', (targetValue, duration = 1600, isCurrency = false) => ({
    displayValue: isCurrency ? 'Rp 0' : '0',
    target: targetValue,
    duration: duration,
    isCurrency: isCurrency,
    hasAnimated: false,
    init() {
        if (this.target <= 0) {
            this.displayValue = this.isCurrency ? 'Rp 0' : '0';
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !this.hasAnimated) {
                this.hasAnimated = true;
                this.startCounting();
                observer.disconnect();
            }
        }, { threshold: 0.15 });

        observer.observe(this.$el);
    },
    startCounting() {
        const start = 0;
        const end = Number(this.target);
        let startTime = null;

        // Kurva Ease-Out Exponential untuk pendaratan angka yang sangat lembut
        const easeOutExpo = (t) => t === 1 ? 1 : 1 - Math.pow(2, -10 * t);

        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / this.duration, 1);
            const current = Math.floor(easeOutExpo(progress) * (end - start) + start);

            if (this.isCurrency) {
                this.displayValue = 'Rp ' + current.toLocaleString('id-ID');
            } else {
                this.displayValue = current.toLocaleString('id-ID');
            }

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                if (this.isCurrency) {
                    this.displayValue = 'Rp ' + end.toLocaleString('id-ID');
                } else {
                    this.displayValue = end.toLocaleString('id-ID');
                }
            }
        };

        window.requestAnimationFrame(step);
    }
}));

// Komponen Interaktif Simulator Palang Pintu Mini di Hero Landing
Alpine.data('heroGateDemo', () => ({
    isOpen: false,
    timer: 0,
    activeEventIndex: 0,
    countdownInterval: null,
    events: [
        {
            icon: '🚗',
            title: 'B 1984 RFS • Honda CR-V',
            subtitle: 'Bpk. Bambang Pamungkas (Blok B4/12)',
            type: 'RFID 13.56MHz Tap',
            status: 'AKSES DIIZINKAN'
        },
        {
            icon: '📦',
            title: 'B 4821 KOP • Motor Kurir',
            subtitle: 'Kurir J&T Express (Paket ke Rumah B2/07)',
            type: 'Scan Tiket QR WhatsApp',
            status: 'TIKET TAMU VALID'
        },
        {
            icon: '🛵',
            title: 'B 6239 PZX • Honda Beat',
            subtitle: 'Ibu Siti Aminah (Blok C1/05)',
            type: 'Tombol HP Buka Gerbang',
            status: 'AKSES DIIZINKAN'
        }
    ],
    init() {
        setInterval(() => {
            if (!this.isOpen) {
                this.activeEventIndex = (this.activeEventIndex + 1) % this.events.length;
            }
        }, 4000);
    },
    triggerOpen() {
        if (this.isOpen) return;
        this.isOpen = true;
        this.timer = 5;

        if (this.countdownInterval) clearInterval(this.countdownInterval);

        this.countdownInterval = setInterval(() => {
            this.timer--;
            if (this.timer <= 0) {
                clearInterval(this.countdownInterval);
                this.isOpen = false;
            }
        }, 1000);
    }
}));

Alpine.start();
