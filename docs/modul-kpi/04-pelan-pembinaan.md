# 04 — Pelan Pembinaan

## 1. Titik permulaan

Produk sedia ada berjalan **atas akaun Google klien**: Apps Script + Google Sheets
untuk data, bot WhatsApp untuk reminder, dan PWA untuk staf. "Data 100% milik anda"
bukan sekadar ayat pemasaran — ia adalah seni bina, dan ia sebahagian daripada
sebab harga RM199 / RM99 boleh bertahan: kos hosting hampir sifar.

Modul KPI menekan seni bina itu pada tiga titik:

1. Ia mengira **wang**. Bonus adalah angka yang orang semak dan pertikaikan.
2. Ia memerlukan rekod yang **tidak boleh diubah** selepas diluluskan.
3. Ia memerlukan **kebenaran per peranan** — staf tidak boleh nampak markah orang
   lain, dan mesti tidak boleh mengubah markahnya sendiri.

Sheets lemah pada ketiga-tiga titik itu. Sesiapa yang mempunyai akses edit kepada
hamparan boleh menukar markah yang telah diluluskan dan sejarah tidak akan
menunjukkannya dengan jelas. Itu bukan kelemahan yang boleh ditampal dengan kod.

## 2. Pilihan seni bina

### Pilihan A — kekal atas Apps Script + Sheets

**Baik:** kos hosting sifar; sejajar dengan janji "data dalam akaun anda"; tiada
model harga baharu; klien boleh lihat data mentah sendiri.

**Buruk:** tiada penguncian rekod yang boleh dipercayai; kebenaran adalah kasar
(per fail, bukan per baris); audit trail boleh diubah oleh sesiapa yang ada akses
edit; kuota masa jalan Apps Script menjadi ketat apabila leaderboard dan laporan
bertambah; kawalan serentak praktikalnya tiada.

**Sesuai jika:** klien menerima bahawa rekod KPI adalah *kertas kerja*, bukan
rekod muktamad, dan pengesahan bonus tetap dibuat secara manual di luar sistem.

### Pilihan B — aplikasi web berpusat *(disyorkan)*

Laravel + Livewire + MySQL, PWA untuk staf, WhatsApp untuk notifikasi.

**Baik:** kebenaran, penguncian, kawalan serentak dan audit trail semuanya
dikuatkuasakan di pelayan; pertanyaan laporan mudah; ujian automatik untuk formula
menjadi mudah; landasan sama untuk ciri akan datang.

**Buruk:** memerlukan hosting dan sandaran; menukar janji "data dalam akaun Google
anda"; memerlukan pelan pemindahan untuk klien sedia ada; menukar model harga.

**Sesuai jika:** KPI hendak menjadi rekod rasmi yang menyokong pembayaran bonus.

### Pilihan C — hibrid

SOP dan reminder kekal atas Sheets; KPI sahaja berpindah ke aplikasi berpusat,
dengan eksport ke Sheets klien untuk ketelusan.

**Baik:** melindungi produk sedia ada; klien masih dapat data mentah.
**Buruk:** dua sistem untuk diselenggara; staf log masuk ke dua tempat melainkan
log masuk disatukan.

### Cadangan

**Pilihan B.** Keperluan yang menentukan ialah `Approved` dan `Locked` mesti benar
— jika markah yang diluluskan boleh diubah tanpa jejak, maka audit trail, aliran
kelulusan dan pengesahan bonus semuanya menjadi hiasan. Itu tidak boleh dicapai
atas Sheets.

Jika kos hosting yang menjadi halangan, Pilihan C adalah kompromi yang munasabah:
ia mengehadkan bahagian berpusat kepada modul yang benar-benar memerlukannya.

Keputusan ini perlu dibuat sebelum sebarang kod ditulis — lihat `05 §K14`.

## 3. Cadangan stack (Pilihan B)

| Lapisan | Pilihan | Sebab |
|---|---|---|
| Rangka kerja | Laravel | Kebenaran, migrasi, ujian, jadual tugasan sudah terbina |
| Antara muka | Blade | Lihat nota di bawah |
| Pangkalan data | MySQL 8 | Kekangan unik & transaksi yang skema ini bergantung padanya |
| Kebenaran | Dasar Laravel per model | Penguatkuasaan di pelayan, bukan menyembunyikan butang |
| Audit | Pendengar peristiwa model → `audit_logs` | Sisip sahaja, seragam |
| Notifikasi | Saluran WhatsApp sedia ada | Guna semula apa yang sudah berjalan |
| Staf | PWA | Sama seperti produk sekarang; staf sudah biasa |
| Eksport | Excel & PDF | Fasa 3 |

Tugasan berjadual yang diperlukan: buka tempoh bulanan, peringatan penilaian
tertunggak, kira semula rumusan cache jika digunakan.

**Nota daripada pembinaan sebenar.** Livewire dicadangkan pada mulanya untuk
jumlah markah yang berubah secara langsung pada borang 25 item. Semasa dibina,
Blade biasa dengan sedikit JavaScript inline memberi hasil yang sama tanpa
menambah dependency, dan ia sepadan dengan produk sedia ada yang memang tiada
dependency runtime. Jumlah markah dirender di pelayan dahulu, kemudian dikemas
kini secara langsung jika JavaScript berjalan — jadi angka tetap betul walaupun
skrip tidak dimuat.

Satu lajur ditambah kepada reka bentuk asal: `staff.is_assessed`. Tidak semua
orang dalam senarai gaji diukur dengan KPI jualan — Supervisor menguruskan kedai
tanpa target jualan sendiri, dan tanpa lajur ini dia muncul di leaderboard pada
RM0.

## 4. Fasa

Anggaran adalah kasar dan mengandaikan seorang pembangun. Ia untuk perancangan,
bukan sebut harga.

### Fasa 0 — keputusan *(tiada kod)*

Jawab 14 soalan dalam `05`. Sepuluh daripadanya mengubah skema pangkalan data.
Menjawabnya sekarang adalah beberapa jam kerja; menjawabnya selepas pembinaan
adalah migrasi data.

### Fasa 1 — MVP

Apa yang diperlukan untuk menjalankan satu bulan penuh dari hujung ke hujung.

- Log masuk, tiga peranan, dasar kebenaran
- Profil staf
- Kemasukan jualan harian + pelarasan
- Rumusan bulanan + markah KPI jualan automatik
- Borang penilaian 25 item dengan penguatkuasaan catatan pada skor 0
- Workflow `Draft → Submitted → Approved → Locked`
- Pengiraan bonus & insentif dengan snapshot
- Papan pemuka KPI staf (paparan sendiri)
- Papan pemuka management (penilaian tertunggak, ringkasan tempoh)
- Audit trail
- Skrin tetapan untuk target, wajaran, gred, kriteria

Anggaran kasar: **4–6 minggu**.

### Fasa 2 — team & pertandingan

- Penilaian KPI team + bonus team
- Leaderboard: jualan, produk fokus, KPI
- Rumusan mingguan
- Notifikasi WhatsApp untuk semua peristiwa workflow
- Carta trend

Anggaran kasar: **2–3 minggu**.

### Fasa 3 — laporan & penghalusan

- Eksport Excel & PDF
- Laporan tahunan & perbandingan tempoh
- Baucar bonus per staf
- Penghalusan PWA, paparan telefon
- Import jualan daripada CSV atau POS

Anggaran kasar: **2–3 minggu**.

**Jangan bina dahulu:** integrasi POS, ramalan, pemarkahan automatik daripada data
kehadiran, aplikasi mudah alih asli. Semuanya bergantung pada sistem yang sudah
digunakan dengan jujur selama beberapa bulan.

## 5. Ujian

Tiga kawasan yang benar-benar penting:

1. **Formula.** Setiap baris dalam `03 §8` menjadi satu ujian, campur kes sempadan
   pada setiap gred bonus (89.99, 90.00, 90.01 dan seterusnya). Ini bahagian
   sistem yang menyentuh pendapatan orang.
2. **Kebenaran.** Ujian yang mengesahkan staf tidak boleh membaca penilaian orang
   lain, tidak boleh mengedit markah sendiri, dan Supervisor tidak boleh
   meluluskan penilaiannya sendiri. Uji di peringkat permintaan, bukan di
   peringkat antara muka.
3. **Mesin keadaan workflow.** Setiap peralihan yang tidak dibenarkan patut ditolak
   dan diuji sebagai ditolak — termasuk mengedit rekod `Locked`.

Data benih patut menghasilkan semula contoh dalam `03 §8` supaya sistem boleh
dibandingkan terus dengan dokumen asal semasa penerimaan.

## 6. Kesan pada model harga

Model sekarang — RM199 tahun pertama, RM99 setahun selepas itu — bergantung pada
kos berulang yang hampir sifar kerana sistem duduk atas akaun Google klien.

Backend berpusat menambah hosting, sandaran dan penyelenggaraan setiap bulan untuk
setiap klien. Angka itu perlu ditampung. Beberapa cara:

- **Add-on berasingan** — SOP kekal RM199/RM99; modul KPI dijual sebagai tambahan
  dengan yuran tahunannya sendiri. Paling mudah difahami klien, dan tidak
  mengganggu tawaran sedia ada.
- **Peringkat lebih tinggi** — pakej "SOP + KPI" pada harga tahunan lebih tinggi.
- **Per staf** — sesuai dengan kos tetapi bertentangan dengan janji "staf tanpa
  had" yang sedang digunakan pada halaman harga.

Cadangan: **add-on berasingan**. Ia mengasingkan modul yang memerlukan hosting
daripada modul yang tidak, mengekalkan pemesejan halaman harga sedia ada tanpa
perlu ditulis semula, dan tidak memaksa klien SOP sedia ada membayar untuk sesuatu
yang mereka tidak guna.

Ini keputusan perniagaan, bukan keputusan teknikal — lihat `05 §K14`.

## 7. Deploy & operasi

- **Persekitaran:** staging dan pengeluaran berasingan. Jangan uji formula bonus
  atas data sebenar.
- **Sandaran:** harian, automatik, luar pelayan, dengan pemulihan yang pernah
  benar-benar diuji. Rekod bonus yang hilang bermakna aduan gaji.
- **Migrasi:** semua perubahan skema melalui fail migrasi berversi, tiada
  pengeditan pangkalan data secara manual.
- **Pemantauan:** log ralat + amaran apabila tugasan berjadual gagal. Peringatan
  penilaian yang senyap-senyap tidak dihantar bermakna bulan tidak ditutup.
- **Simpanan data:** ikut tempoh yang ditetapkan dalam `05 §K13`; buang data
  melebihi tempoh itu.
