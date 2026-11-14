@echo off
chcp 65001 >nul
echo ==========================================================
echo        ⚡ E-WARGA - Instant 1-Second VPS Deployment
echo ==========================================================
echo.
echo 🚀 Menghubungi VPS (43.157.230.29)...
if exist "id_ed25519_ewarga.key" (
    ssh.exe -i id_ed25519_ewarga.key -o StrictHostKeyChecking=no -o ConnectTimeout=5 ubuntu@43.157.230.29 "sudo bash -c 'cd /home/unique-monsters-ewarga/htdocs/e-warga.unique-monsters.my.id && git fetch origin main --quiet && git reset --hard origin/main --quiet && php artisan optimize:clear --quiet && php artisan config:cache --quiet && php artisan route:cache --quiet && php artisan view:cache --quiet && chown -R unique-monsters-ewarga:unique-monsters-ewarga storage bootstrap/cache public/build 2>/dev/null'"
    echo.
    echo ==========================================================
    echo 🎉 DEPLOYMENT 1 DETIK SUKSES!
    echo 🌐 Website Live: https://e-warga.unique-monsters.my.id/
    echo ==========================================================
) else (
    echo ❌ Berkas kunci id_ed25519_ewarga.key tidak ditemukan di folder lokal.
)
echo.
pause

