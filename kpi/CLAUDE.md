# Modul KPI — nota untuk pembangun

Aplikasi Laravel untuk KPI, prestasi dan bonus staf. Reka bentuknya didokumen
dalam `../docs/modul-kpi/` — baca dokumen itu sebelum mengubah formula atau skema.

## Peraturan yang tidak boleh dilanggar

Ini bukan gaya kod, ini keperluan sistem. Semuanya ada ujian.

1. **Wang dalam sen (integer).** Tiada nombor apungan untuk ringgit, di mana-mana.
   Guna `App\Support\Money`.
2. **Formula hanya wujud dalam `app/Services/ScoreCalculator.php` dan
   `RewardCalculator.php`.** Jangan salin logik pengiraan ke controller atau Blade.
3. **Tempoh mengira daripada `config_snapshot`, bukan tetapan semasa.** Menaikkan
   target hari ini tidak boleh menulis semula bulan yang sudah dibayar.
4. **`approved` dan `locked` bermakna tidak boleh diubah.** Satu-satunya jalan
   keluar ialah `reopen`, dan ia direkod.
5. **Penilai ≠ pelulus.** Dikuatkuasakan di `AssessmentWorkflow` dan di policy.
6. **`audit_logs` sisip sahaja.** Tiada kemas kini, tiada padam, termasuk Admin.
7. **Kebenaran dikuatkuasakan di pelayan.** Menyembunyikan butang bukan kawalan
   akses — ujian dalam `tests/Feature/PermissionTest.php` menyemak melalui HTTP.

## Struktur

```
app/Services/        Semua logik domain: pengiraan, workflow, tempoh, ganjaran, audit
app/Policies/        Kebenaran per model
app/Enums/           Status dan peranan
database/data/       Teks 25 kriteria penilaian (tiga gred setiap satu)
database/seeders/    ConfigurationSeeder (produksi) + DemoSeeder/DemoCycleSeeder (demo)
resources/views/     Blade, CSS inline, Bahasa Melayu, mobile-first
```

## Arahan

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed                      # business, tetapan, 25 kriteria, satu admin
php artisan db:seed --class=DemoSeeder          # staf contoh + dua bulan jualan
php artisan db:seed --class=DemoCycleSeeder     # satu bulan dikunci, satu sedang berjalan
php artisan serve
./vendor/bin/phpunit                            # mesti hijau sebelum push
```

## Konfigurasi klien

Nama bisnes, target dan PIN admin datang daripada `.env` — bukan daripada kod.
Repo ini public, jadi jangan commit angka atau nama klien.

## Belum dibuat (Fasa 2 dan 3)

Borang penilaian KPI team (data dan formula sudah ada, skrin belum), notifikasi
WhatsApp, rumusan mingguan pada UI, carta trend, eksport Excel/PDF, import POS.
