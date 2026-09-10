# Modul KPI, Prestasi & Bonus Staf

Aplikasi Laravel untuk merekod jualan harian, menilai prestasi staf, mengira
bonus, dan menutup bulan dengan kelulusan serta audit trail.

Ini **Fasa 1** daripada reka bentuk dalam [`../docs/modul-kpi/`](../docs/modul-kpi/).

## Apa yang ia buat

- **Jualan harian** — satu rekod per staf per hari, dengan rekod pelarasan
  berasingan untuk pemulangan. Angka mingguan dan bulanan sentiasa dijumlahkan
  daripada rekod harian, tidak pernah dimasukkan berasingan.
- **KPI individu** — 40% daripada jualan (dikira automatik, had 40), 60% daripada
  25 item yang dinilai Supervisor pada skala 0/1/2. Skor 0 mesti ada catatan.
- **KPI team** — struktur sama, diukur terhadap target team.
- **Ganjaran** — bonus KPI, insentif jualan tinggi dan bonus team, ketiga-tiganya
  berasingan dan dijumlahkan. Setiap angka menyimpan sebab ia jadi begitu.
- **Workflow** — `Draf → Dihantar → Diluluskan → Dikunci`, dengan `Dibuka semula`
  sebagai satu-satunya jalan balik, dan setiap langkah direkod.
- **Leaderboard** — jualan, produk fokus dan KPI; harian hingga tahunan.
- **Audit trail** — sisip sahaja, tiada laluan untuk mengubah atau memadam.

## Jalankan

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan db:seed --class=DemoSeeder
php artisan db:seed --class=DemoCycleSeeder
php artisan serve
```

Log masuk demo: kod `admin`, PIN `123456` (Admin) · `sv` (Supervisor) ·
`s001`–`s005` (Staf). **Tukar PIN sebelum digunakan sebenar.**

## Ujian

```bash
./vendor/bin/phpunit
```

70 ujian. Yang penting: setiap contoh pengiraan dalam dokumen reka bentuk,
setiap sempadan gred bonus, setiap peralihan workflow yang tidak dibenarkan, dan
setiap sempadan kebenaran (staf tidak boleh baca rekod orang lain, penilai tidak
boleh luluskan penilaian sendiri, rekod yang diluluskan tidak boleh diedit).

## Nota

Laravel 13 · PHP 8.4 · SQLite untuk pembangunan, MySQL untuk pengeluaran ·
Blade dengan CSS inline, tiada build step · Bahasa Melayu · mobile-first.

Konfigurasi klien (nama bisnes, target, PIN admin) datang daripada `.env`.
Repo ini public — jangan commit angka atau nama klien.
