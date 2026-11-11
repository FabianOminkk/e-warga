#!/usr/bin/env bash
# ==============================================================================
#  E-WARGA - Automated Production Deployment Script
#  Kompatibel dengan VPS (Ubuntu/Debian) & GitHub Actions CI/CD
#  Mendukung Arsitektur Terpisah: frontend/ (UI/UX) & backend/ (Database & API)
# ==============================================================================

set -e # Hentikan eksekusi jika terjadi error kritis

echo "=========================================================="
echo "🚀 [$(date '+%Y-%m-%d %H:%M:%S')] Memulai Deployment E-WARGA..."
echo "=========================================================="

# 1. Pastikan berada di root direktori project
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 2. Periksa keberadaan file konfigurasi .env backend
if [ ! -f backend/.env ]; then
    echo "⚠️  File backend/.env tidak ditemukan! Menyalin dari backend/.env.example..."
    cp backend/.env.example backend/.env
    php artisan key:generate --force
fi

# 3. Pastikan database SQLite siap jika menggunakan driver sqlite
if grep -q "DB_CONNECTION=sqlite" backend/.env 2>/dev/null || ! grep -q "DB_CONNECTION=" backend/.env 2>/dev/null; then
    if [ ! -f backend/database/database.sqlite ]; then
        echo "📦 Membuat file database SQLite (backend/database/database.sqlite)..."
        touch backend/database/database.sqlite
    fi
    chmod 664 backend/database/database.sqlite 2>/dev/null || true
fi

# 4. Aktifkan Maintenance Mode agar pengunjung tidak melihat proses update
echo "🔒 Mengaktifkan Maintenance Mode..."
php artisan down --retry=15 --refresh=5 || true

# 5. Sinkronisasi kode terbaru dari cabang main GitHub
echo "📥 Menarik perubahan kode terbaru dari GitHub (origin/main)..."
git fetch origin main
git reset --hard origin/main

# 6. Install dependensi Backend PHP (Composer)
echo "📦 Menginstall dependensi Backend Composer..."
cd "$SCRIPT_DIR/backend"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
cd "$SCRIPT_DIR"

# 7. Eksekusi migrasi database jika ada tabel baru
echo "🗄️  Menjalankan migrasi database..."
php artisan migrate --force

# 8. Kompilasi aset Frontend (Vite Tailwind CSS & JS)
if command -v npm &> /dev/null; then
    echo "🎨 Mengompilasi aset Frontend (Vite)..."
    cd "$SCRIPT_DIR/frontend"
    if [ -f package-lock.json ]; then
        npm ci --no-audit --prefer-offline 2>/dev/null || npm install
    else
        npm install
    fi
    npm run build
    cd "$SCRIPT_DIR"
else
    echo "⚠️  NPM tidak ditemukan, melewatkan kompilasi frontend lokal."
fi

# 9. Bersihkan dan segarkan cache performa Laravel Backend
echo "⚡ Mengoptimalkan cache sistem Laravel Backend..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

# 10. Pastikan storage symlink terhubung
echo "🔗 Menghubungkan storage symlink..."
php artisan storage:link 2>/dev/null || true

# 11. Perbaiki permission folder storage & bootstrap
echo "🛡️  Mengatur permission file & folder..."
chmod -R 775 backend/storage backend/bootstrap/cache 2>/dev/null || true
if id "www-data" &>/dev/null; then
    chown -R www-data:www-data backend/storage backend/bootstrap/cache backend/public/uploads 2>/dev/null || true
fi

# 12. Restart antrian worker jika aktif
php artisan queue:restart 2>/dev/null || true

# 13. Matikan Maintenance Mode (Sistem kembali online)
echo "🔓 Mematikan Maintenance Mode..."
php artisan up

echo "=========================================================="
echo "🎉 [$(date '+%Y-%m-%d %H:%M:%S')] DEPLOYMENT BERHASIL & WEBSITE LIVE!"
echo "=========================================================="
