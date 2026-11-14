@echo off
chcp 65001 >nul
echo ==========================================================
echo        ⚡ E-WARGA - Turbo Sync & Instant VPS Deploy
echo ==========================================================
echo.
git status --short
echo.
git add .
set /p commit_msg="Masukkan pesan update (tekan Enter untuk default): "
if "%commit_msg%"=="" set commit_msg="update: sync project changes to production"
git commit -m "%commit_msg%"
echo.
echo [1/2] 📤 Mengupload perubahan ke GitHub (Branch main)...
git push origin main
echo.
echo [2/2] ⚡ Memicu Turbo Deploy langsung ke VPS (~1 detik)...
if exist "id_ed25519_ewarga.key" (
    ssh.exe -i id_ed25519_ewarga.key -o StrictHostKeyChecking=no -o ConnectTimeout=5 ubuntu@43.157.230.29 "sudo bash -c 'cd /home/unique-monsters-ewarga/htdocs/e-warga.unique-monsters.my.id && git fetch origin main --quiet && git reset --hard origin/main --quiet && php artisan optimize:clear --quiet && php artisan config:cache --quiet && php artisan route:cache --quiet && php artisan view:cache --quiet && chown -R unique-monsters-ewarga:unique-monsters-ewarga storage bootstrap/cache public/build 2>/dev/null'"
    echo.
    echo ==========================================================
    echo 🎉 DEPLOYMENT KILAT SELESAI! (~1-2 DETIK)
    echo 🌐 Website Live: https://e-warga.unique-monsters.my.id/
    echo ==========================================================
) else (
    echo [INFO] GitHub Actions akan menyelesaikan deployment di cloud.
    echo 🔗 Pantau: https://github.com/FabianOminkk/e-warga/actions
)
echo.
pause

