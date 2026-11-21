#!/usr/bin/env bash
# ==============================================================================
#  E-WARGA - Automated Production Deployment Script
#  Kompatibel dengan VPS (Ubuntu/Debian) & CloudPanel (Decoupled FE/BE)
# ==============================================================================

set -e # Hentikan eksekusi jika terjadi error kritis

echo "=========================================================="
echo "🚀 [$(date '+%Y-%m-%d %H:%M:%S')] Memulai Deployment E-WARGA..."
echo "=========================================================="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 1. Pastikan file .env ada di root dan backend
if [ ! -f .env ] && [ -f backend/.env ]; then
    cp backend/.env .env
elif [ ! -f .env ]; then
    echo "⚠️  File .env tidak ditemukan! Menyalin dari .env.example..."
    cp .env.example .env
fi

if [ -d "backend" ] && [ ! -f backend/.env ]; then
    cp .env backend/.env
fi

# 2. Pastikan symlink public tersedia untuk Nginx CloudPanel
if [ ! -e "public" ] && [ -d "backend/public" ]; then
    echo "🔗 Menghubungkan symlink public -> backend/public untuk Nginx..."
    ln -s backend/public public
fi

# 3. Aktifkan Maintenance Mode
echo "🔒 Mengaktifkan Maintenance Mode..."
php artisan down --retry=15 --refresh=5 || true

# 4. Sinkronisasi kode dari GitHub
echo "📥 Menarik kode terbaru dari branch main..."
git config core.fileMode false
git pull --ff-only origin main

# 5. Install dependensi Composer (Backend)
if [ -d "backend" ]; then
    echo "📦 Menginstall dependensi Composer (backend)..."
    cd backend
    composer install --no-dev --optimize-autoloader --no-interaction
    cd "$SCRIPT_DIR"
else
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# 6. Migrasi database
echo "🗄️  Menjalankan migrasi database..."
php artisan migrate --force

# 7. Kompilasi aset Frontend
if [ -d "frontend" ] && command -v npm &> /dev/null; then
    echo "🎨 Mengompilasi aset Frontend (Vite di frontend/)..."
    cd frontend
    npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
    npm run build
    cd "$SCRIPT_DIR"
elif command -v npm &> /dev/null; then
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
chmod -R 775 backend/storage backend/bootstrap/cache 2>/dev/null || chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 11. Matikan Maintenance Mode
echo "🔓 Mematikan Maintenance Mode..."
php artisan up

echo "=========================================================="
echo "🎉 [$(date '+%Y-%m-%d %H:%M:%S')] DEPLOYMENT BERHASIL & WEBSITE LIVE!"
echo "=========================================================="
