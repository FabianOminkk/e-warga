# 🚀 Panduan Lengkap Deploy E-WARGA ke VPS & Auto-Update via GitHub CI/CD

> **Tujuan**: Setiap kali Anda melakukan perubahan kode di laptop dan melakukan `git push` ke GitHub, server VPS yang terhubung ke domain Anda akan **secara otomatis memperbarui website secara instan tanpa perlu login manual ke VPS**.

---

## 🏗️ Alur Kerja Sistem (Workflow Overview)

```text
┌─────────────────────────┐          ┌───────────────────────────┐          ┌───────────────────────────┐
│     LAPTOP DEVELOPER    │          │     GITHUB REPOSITORY     │          │         SERVER VPS        │
│                         │          │                           │          │   (Domain: namadomain.com)│
│  Edit Kode / Fitur Baru ├─────────►│  Branch: main             ├─────────►│  GitHub Actions via SSH   │
│  Jalankan sync-github   │ git push │  Trigger: .github/deploy  │ Auto-Run │  Menjalankan: deploy.sh   │
└─────────────────────────┘          └───────────────────────────┘          └─────────────┬─────────────┘
                                                                                          │
                                                                                          ▼
                                                                           ┌───────────────────────────┐
                                                                           │   WEBSITE LIVE TERUPDATE! │
                                                                           │   - Git Pull & Composer   │
                                                                           │   - Migrate Database      │
                                                                           │   - Build Vite (CSS/JS)   │
                                                                           │   - Optimize Cache        │
                                                                           └───────────────────────────┘
```

---

## 📋 Prasyarat (Requirements)

1. **VPS**: Ubuntu 22.04 LTS atau 24.04 LTS (DigitalOcean, Hostinger, DomaiNesia, Niagahoster, Biznet, AWS, dll).
2. **Domain**: Domain aktif yang sudah dibeli (misal: `ewarga-cluster.id` atau `namadomain.com`).
3. **Akun GitHub**: Repositori GitHub project `e-warga`.

---

## 🌐 LANGKAH 1: Sambungkan Domain ke IP VPS (DNS Setup)

1. Buka dashboard tempat Anda membeli domain (DNS Management / Cloudflare).
2. Tambahkan **DNS Records**:

| Tipe | Nama / Host | Target / Nilai IPv4 | TTL | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **A** | `@` | `IP_PUBLIK_VPS_ANDA` | Auto / 300 | Domain utama (`namadomain.com`) |
| **A** | `www` | `IP_PUBLIK_VPS_ANDA` | Auto / 300 | Subdomain www (`www.namadomain.com`) |

> ⏳ *Catatan: Propagasi DNS biasanya memakan waktu antara 5 menit hingga 1 jam.*

---

## 🖥️ LANGKAH 2: Persiapan Server VPS (Install PHP 8.3, Nginx & Node.js)

Login ke VPS Anda via Terminal atau PuTTY:
```bash
ssh root@IP_PUBLIK_VPS_ANDA
```

Jalankan rangkaian perintah berikut satu per satu:

### 1. Update Paket Sistem & Tambah Swap (Sangat disarankan untuk RAM 1GB - 2GB)
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip ufw software-properties-common nginx

# Buat Swap Memory 2GB agar proses build frontend lancar tanpa kehabisan RAM
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

### 2. Install PHP 8.3 & Ekstensi yang Dibutuhkan Laravel
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-mbstring \
                    php8.3-xml php8.3-curl php8.3-sqlite3 php8.3-mysql \
                    php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd
```

### 3. Install Composer (PHP Dependency Manager)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 4. Install Node.js 20 & NPM (Untuk Kompilasi Vite)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 5. Install Certbot (Untuk SSL HTTPS Gratis Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
```

---

## 📂 LANGKAH 3: Clone Project E-WARGA ke Direktori VPS

Di dalam server VPS:

```bash
# 1. Pindah ke direktori web server
cd /var/www

# 2. Clone repositori project
sudo git clone https://github.com/FabianOminkk/e-warga.git

# 3. Masuk ke folder project
cd /var/www/e-warga

# 4. Buat file .env produksi di backend
sudo cp backend/.env.example backend/.env

# 5. Pasang dependensi Backend (PHP) & Generate App Key
cd /var/www/e-warga/backend
sudo composer install --no-dev --optimize-autoloader
cd /var/www/e-warga
sudo php artisan key:generate

# 6. Siapkan Database SQLite (atau MySQL)
sudo touch backend/database/database.sqlite
sudo php artisan migrate --force --seed

# 7. Kompilasi Aset Frontend (UI/UX)
cd /var/www/e-warga/frontend
sudo npm install
sudo npm run build
cd /var/www/e-warga

# 8. Buat Symlink Storage
sudo php artisan storage:link

# 9. Atur Hak Akses Folder (PENTING AGAR TIDAK ERROR PERMISSION)
sudo chown -R www-data:www-data /var/www/e-warga
sudo chmod -R 775 /var/www/e-warga/backend/storage /var/www/e-warga/backend/bootstrap/cache /var/www/e-warga/backend/database
sudo chmod +x /var/www/e-warga/deploy.sh
```

### Edit File `.env` di VPS:
Buka file konfigurasi `.env`:
```bash
sudo nano backend/.env
```
Sesuaikan bagian ini:
```env
APP_NAME=E-WARGA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomain.com

DB_CONNECTION=sqlite
```
*(Tekan `Ctrl + O`, `Enter` lalu `Ctrl + X` untuk menyimpan).*

---

## 🌐 LANGKAH 4: Konfigurasi Nginx & Pasang SSL HTTPS

### 1. Buat Konfigurasi Virtual Host Nginx
```bash
sudo nano /etc/nginx/sites-available/e-warga
```

Paste konfigurasi berikut (ganti `namadomain.com` dengan domain Anda):
```nginx
server {
    listen 80;
    server_name namadomain.com www.namadomain.com;
    root /var/www/e-warga/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    # Batas upload file KTP / Bukti Transfer kas
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Aktifkan Konfigurasi & Restart Nginx
```bash
sudo ln -s /etc/nginx/sites-available/e-warga /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default 2>/dev/null || true
sudo nginx -t
sudo systemctl restart nginx
```

### 3. Pasang SSL Gratis (HTTPS Gembok Hijau)
```bash
sudo certbot --nginx -d namadomain.com -d www.namadomain.com
```
*Ikuti petunjuk di layar (masukkan email dan pilih redirect HTTP to HTTPS).*

Buka browser dan buka `https://namadomain.com`. Website E-WARGA kini sudah live dan beroperasi penuh! 🎉

---

## 🔄 LANGKAH 5: Konfigurasi Otomatisasi GitHub Actions (Auto-Deploy)

Agar setiap `git push` otomatis memperbarui VPS Anda:

### 1. Buat Kunci SSH di Server VPS
Di server VPS, jalankan:
```bash
ssh-keygen -t ed25519 -C "github-actions-ewarga" -f ~/.ssh/github_deploy -N ""
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

Tampilkan isi Private Key yang baru dibuat:
```bash
cat ~/.ssh/github_deploy
```
*Salin seluruh teks outputnya (mulai dari `-----BEGIN OPENSSH PRIVATE KEY-----` sampai `-----END OPENSSH PRIVATE KEY-----`).*

### 2. Daftarkan Secrets di Repositori GitHub
1. Buka repositori Anda di GitHub: `https://github.com/FabianOminkk/e-warga`
2. Klik tab **Settings** ⚙️ &rarr; di menu kiri pilih **Secrets and variables** &rarr; klik **Actions**.
3. Klik tombol hijau **New repository secret**, tambahkan variabel berikut:

| Nama Secret | Nilai (Value) | Contoh |
| :--- | :--- | :--- |
| `VPS_HOST` | IP Publik VPS Anda | `103.186.201.45` |
| `VPS_USERNAME` | Username login SSH | `root` (atau `ubuntu`) |
| `VPS_SSH_KEY` | Teks Private Key yang disalin di atas | `-----BEGIN OPENSSH PRIVATE KEY...` |
| `VPS_PORT` | Port SSH server Anda | `22` |
| `VPS_TARGET_DIR` | Folder letak aplikasi di VPS | `/var/www/e-warga` |

*(Jika VPS Anda menggunakan login password tanpa SSH key, Anda cukup membuat secret `VPS_PASSWORD` dan mengisi kata sandi root).*

---

## 🎯 LANGKAH 6: Pembuktian Auto-Update (Testing Deployment)

Sekarang alur otomatisasi sudah aktif 100%! Mari kita uji:

1. Di laptop Anda, lakukan perubahan kode atau tambah fitur.
2. Simpan dan push ke GitHub (bisa double click file `sync-github.bat` di root project):
   ```bash
   git add .
   git commit -m "feat: pembaruan fitur baru"
   git push origin main
   ```
3. Buka tab **Actions** di GitHub repository Anda:
   - Anda akan melihat workflow **"Auto Deploy E-WARGA to VPS"** berjalan secara otomatis.
   - Dalam ~30 detik status akan berubah menjadi centang hijau ✅.
4. Refresh website Anda di browser (`https://namadomain.com`):
   - **Perubahan langsung muncul di website live secara otomatis!**

---

## 📡 Integrasi IoT Hardware (ESP32) dengan Domain Live

Setelah domain live aktif (`https://namadomain.com`), Anda dapat menghubungkan alat fisik ESP32 palang pintu ke website domain Anda:

1. Buka file firmware Arduino di [`firmware/esp32_smart_gate/esp32_smart_gate.ino`](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/firmware/esp32_smart_gate/esp32_smart_gate.ino).
2. Ubah baris konfigurasi server:
   ```cpp
   // Dari IP lokal lama:
   // const char* serverUrl = "http://192.168.1.100:8000/api/v1/gate";

   // Ganti menjadi domain live produksi:
   const char* serverUrl = "https://namadomain.com/api/v1/gate";
   ```
3. Flash kembali sketch ke ESP32. Palang pintu fisik Anda sekarang terhubung langsung ke cloud domain secara global!

---

## 🛠️ Perintah Berguna Saat di VPS

| Kebutuhan | Perintah di VPS (`/var/www/e-warga`) |
| :--- | :--- |
| **Deploy Manual Seketika** | `sudo ./deploy.sh` |
| **Cek Log Error Laravel** | `tail -f storage/logs/laravel.log` |
| **Cek Log Web Server Nginx** | `tail -f /var/log/nginx/error.log` |
| **Reset Cache Manual** | `sudo php artisan optimize:clear` |
| **Restart Web Server** | `sudo systemctl restart nginx php8.3-fpm` |

