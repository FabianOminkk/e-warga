@echo off
echo ==============================================
echo        E-WARGA - Auto Sync to GitHub
echo ==============================================
git status --short
git add .
set /p commit_msg="Masukkan pesan commit (tekan enter untuk pesan otomatis): "
if "%commit_msg%"=="" set commit_msg="update: sync project changes to github"
git commit -m "%commit_msg%"
git push origin main
echo.
echo ==============================================
echo  Sync Berhasil! https://github.com/FabianOminkk/e-warga
echo ==============================================
pause
