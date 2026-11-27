param(
    [string]$Message = "chore(sync): update aplikasi dan sinkron ke produksi",
    [switch]$SkipBuild,
    [switch]$IncludeFirmware
)

$ErrorActionPreference = "Stop"
$RepoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $RepoRoot

$LockFile = Join-Path $RepoRoot ".git\auto-sync-web.lock"
if (Test-Path $LockFile) {
    $ageMinutes = ((Get-Date) - (Get-Item $LockFile).LastWriteTime).TotalMinutes
    if ($ageMinutes -lt 5) {
        Write-Warning "Sinkronisasi lain sedang berjalan (lock < 5 menit). Membatalkan untuk mencegah bentrok."
        exit 1
    }
    Remove-Item $LockFile -Force -ErrorAction SilentlyContinue
}

try {
    New-Item -ItemType File -Path $LockFile -Force | Out-Null

    # 1. Kumpulkan file berubah & file baru tanpa masalah parsing porcelain
    $trackedChanged = @(git diff --name-only HEAD 2>$null)
    $untrackedFiles = @(git ls-files --others --exclude-standard 2>$null)
    $allChanged = @($trackedChanged + $untrackedFiles | Where-Object { $_ -and $_ -notmatch '^(scratch/|firmware/)' } | Select-Object -Unique)

    # 2. Validasi sintaks seluruh file PHP yang berubah
    $phpFiles = $allChanged | Where-Object { $_ -match '\.php$' -and (Test-Path $_) }
    foreach ($f in $phpFiles) {
        php -l $f | Out-Null
        if ($LASTEXITCODE -ne 0) {
            throw "PHP lint error pada $f. Sinkronisasi dibatalkan agar produksi tidak rusak."
        }
    }

    # 3. Rebuild frontend Vite bila ada perubahan di frontend/
    if (-not $SkipBuild) {
        $hasFrontendChange = $allChanged | Where-Object { $_ -match '^frontend/' }
        if ($hasFrontendChange) {
            Push-Location (Join-Path $RepoRoot "frontend")
            npm run build
            if ($LASTEXITCODE -ne 0) {
                Pop-Location
                throw "Vite build gagal. Perbaiki error frontend terlebih dahulu."
            }
            Pop-Location
        }
    }

    # 4. Stage perubahan aplikasi dengan aman (mengecualikan scratch & firmware final kecuali diminta)
    if ($IncludeFirmware) {
        git add -A -- . ':!scratch' ':!*.log'
    } else {
        git add -A -- . ':!scratch' ':!firmware' ':!*.log'
    }

    git diff --cached --quiet
    if ($LASTEXITCODE -eq 0) {
        Write-Output "Tidak ada perubahan staged baru untuk di-commit."
        exit 0
    }

    # 5. Sinkron aman dengan origin/main sebelum push agar anti-bentrok
    git fetch origin main
    git commit -m $Message
    git pull --rebase --autostash origin main
    if ($LASTEXITCODE -ne 0) {
        throw "Rebase dengan origin/main gagal. Selesaikan konflik secara manual."
    }

    git push origin main
    if ($LASTEXITCODE -ne 0) {
        throw "git push origin main gagal."
    }

    Write-Output "Sukses push ke origin/main. GitHub Actions CI/CD akan otomatis men-deploy ke web live."
}
finally {
    if (Test-Path $LockFile) {
        Remove-Item $LockFile -Force -ErrorAction SilentlyContinue
    }
}
