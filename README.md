# Medika Landing Page

Website company profile PT Interskala Medika Indonesia dengan CMS untuk mengelola konten, produk, dokumentasi, karier, media, dan SEO. Website mendukung Bahasa Indonesia, Inggris, dan Mandarin.

## Persyaratan

- PHP 8.3 atau lebih baru dan Composer
- Node.js dan npm
- SQLite (database bawaan) atau database lain yang dikonfigurasi di `.env`

## Setup lokal

```powershell
composer install
Copy-Item .env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate
php artisan cms:migrate-public-media
php artisan db:seed --class=RoleSeeder
php artisan cms:create-superadmin "Superadmin" admin@example.com
npm install
npm run build
```

Perintah pembuatan superadmin akan meminta password secara interaktif. Setelah setup, jalankan aplikasi:

```powershell
php artisan serve
```

Buka `http://127.0.0.1:8000`, dan akses CMS melalui `/admin/login`. Saat mengembangkan frontend, jalankan `npm run dev` di terminal terpisah.

## Scheduler

Lowongan yang lewat tanggal penutupan otomatis menjadi Closed; thumbnail gambar yang gagal dibuat saat upload dicoba ulang setiap jam. Keduanya membutuhkan Laravel Scheduler. Untuk pengembangan lokal, jalankan scheduler di terminal terpisah:

```powershell
php artisan schedule:work
```

Di server Linux, daftarkan cron berikut agar Laravel memeriksa jadwal setiap menit:

```cron
* * * * * cd /path/to/medikalandingpage && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path/to/medikalandingpage` dengan direktori aplikasi di server. Verifikasi jadwal terdaftar dengan `php artisan schedule:list`.

## Penyimpanan media

Gambar dan video CMS disimpan di `storage/app/public/images` dan `storage/app/public/videos`; brosur disimpan di `storage/app/public/brochures`. Jalankan `php artisan cms:migrate-public-media` sekali setelah memperbarui kode untuk memindahkan aset lama dari `public/images`, `public/brochures`, dan folder media lama serta menyinkronkannya ke Media Library. File dilayani melalui route Laravel, jadi jangan jalankan `php artisan storage:link` untuk folder ini; akses Draft diperiksa oleh aplikasi.

## Backup dan pemulihan

Backup database dan seluruh `storage/app/public` setiap hari. Di server Linux, siapkan akses `mysqldump`, `mysql`, `tar`, dan `sha256sum`; pasang juga `sqlite3` jika database yang digunakan SQLite. Jadwalkan backup pukul 02.00 waktu server:

```cron
0 2 * * * cd /path/to/medikalandingpage && set -a && . ./.env && set +a && bash scripts/backup-cms.sh >> storage/logs/cms-backup.log 2>&1
```

Secara default, backup disimpan di `storage/app/private/backups`; isi `.env` tidak disertakan. Atur `CMS_BACKUP_PATH` di environment server untuk menyimpan backup di volume terpisah, di luar direktori aplikasi. Simpan backup sesuai kebijakan retensi Tim IT dan batasi aksesnya karena dump database memuat data admin.

Uji restore tiap tiga bulan pada server staging yang memiliki akses ke folder backup dan database terisolasi. Perintah berikut memverifikasi checksum, mengekstrak media ke direktori sementara, lalu memulihkan SQL ke database bernama sementara dan menghapus database uji itu setelah selesai. Database staging harus mengizinkan pembuatan/penghapusan database; perintah ini tidak mengubah database aplikasi.

```bash
backup="$(find "${CMS_BACKUP_PATH:-storage/app/private/backups}" -mindepth 1 -maxdepth 1 -type d | sort | tail -n 1)"
bash scripts/verify-cms-backup.sh "$backup"
```

Jadwalkan pemeriksaan triwulanan di staging setelah volume backup dipasang dan `.env` staging dimuat:

```cron
0 3 1 */3 * cd /path/to/medikalandingpage && set -a && . ./.env && set +a && backup="$(find "${CMS_BACKUP_PATH:-storage/app/private/backups}" -mindepth 1 -maxdepth 1 -type d | sort | tail -n 1)" && bash scripts/verify-cms-backup.sh "$backup" >> storage/logs/cms-restore-check.log 2>&1
```

Untuk pemulihan aplikasi setelah insiden, deploy versi kode yang sesuai, pulihkan dump ke database aplikasi, lalu ekstrak arsip media ke `storage/app/public`. Jangan arahkan prosedur uji restore ke database produksi.
