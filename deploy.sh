#!/usr/bin/env bash
# ==============================================================================
#  E-WARGA - Automated Production Deployment Script
#  Kompatibel dengan VPS (Ubuntu/Debian) & CloudPanel
# ==============================================================================

set -e # Hentikan eksekusi jika terjadi error kritis

echo "=========================================================="
echo "🚀 [$(date '+%Y-%m-%d %H:%M:%S')] Memulai Deployment E-WARGA..."
echo "=========================================================="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 1. Pastikan file .env ada
if [ ! -f .env ]; then
    echo "⚠️  File .env tidak ditemukan! Menyalin dari .env.example..."
    cp .env.example .env
    php artisan key:generate --force
fi

# 2. Pastikan database SQLite siap jika memakai sqlite
if grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null || ! grep -q "DB_CONNECTION=" .env 2>/dev/null; then
    if [ ! -f database/database.sqlite ]; then
        echo "📦 Membuat file database SQLite (database/database.sqlite)..."
        touch database/database.sqlite
    fi
    chmod 664 database/database.sqlite 2>/dev/null || true
fi

# 3. Aktifkan Maintenance Mode
echo "🔒 Mengaktifkan Maintenance Mode..."
php artisan down --retry=15 --refresh=5 || true

# 4. Sinkronisasi kode dari GitHub
echo "📥 Menarik kode terbaru dari branch main..."
git config core.fileMode false
git pull --ff-only origin main

# 5. Install dependensi Composer
echo "📦 Menginstall dependensi Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# 6. Migrasi database
echo "🗄️  Menjalankan migrasi database..."
php artisan migrate --force

# 7. Kompilasi aset Frontend
if command -v npm &> /dev/null; then
    echo "🎨 Mengompilasi aset Frontend (Vite)..."
    npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
    npm run build
fi

# 8. Bersihkan dan segarkan cache Laravel
echo "⚡ Mengoptimalkan cache sistem..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Storage link
php artisan storage:link 2>/dev/null || true

# 10. Permission
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 11. Matikan Maintenance Mode
echo "🔓 Mematikan Maintenance Mode..."
php artisan up

echo "=========================================================="
echo "🎉 [$(date '+%Y-%m-%d %H:%M:%S')] DEPLOYMENT BERHASIL & WEBSITE LIVE!"
echo "=========================================================="
