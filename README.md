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
