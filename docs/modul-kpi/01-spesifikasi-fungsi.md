# 01 — Spesifikasi Fungsi

## 1. Skop

**Dalam skop**

- Rekod jualan harian per staf (nilai RM + kuantiti produk fokus).
- Rumusan mingguan & bulanan yang dijana automatik daripada rekod harian.
- Penilaian KPI individu: 40% automatik daripada jualan, 60% dinilai Supervisor.
- Penilaian KPI team.
- Pengiraan bonus KPI individu, insentif jualan tinggi dan bonus team.
- Leaderboard & dashboard (harian, mingguan, bulanan, tahunan).
- Workflow kelulusan berperingkat dengan penguncian rekod.
- Audit trail penuh untuk setiap perubahan markah dan status.
- Laporan bertapis serta eksport.

**Luar skop (fasa ini)**

- Integrasi POS automatik — jualan dimasukkan secara manual buat masa ini.
  Reka bentuk data menyediakan ruang untuk import kemudian (lihat `02 §3.1`).
- Pengiraan gaji atau slip gaji. Modul ini mengira **bonus** sahaja; bonus bukan
  sebahagian gaji asas dan tidak diserahkan kepada mana-mana sistem payroll.
- Sistem kehadiran (punch card). Markah disiplin dinilai Supervisor secara manual;
  jika sistem kehadiran wujud kemudian ia boleh menyuap komponen ini.
- Pengurusan stok atau inventori.

## 2. Peranan & kebenaran

Tiga peranan, sejajar dengan produk sedia ada (staf sudah pun ada PIN sendiri).

| Keupayaan | Staff | Supervisor | Admin / Management |
|---|:--:|:--:|:--:|
| Lihat KPI, jualan & bonus **sendiri** | ✅ | ✅ | ✅ |
| Lihat KPI staf lain | ❌ | ✅ | ✅ |
| Lihat leaderboard | ✅ ¹ | ✅ | ✅ |
| Masukkan / betulkan jualan harian | ❌ ² | ✅ | ✅ |
| Beri markah KPI 60% | ❌ | ✅ | ✅ |
| Hantar penilaian untuk kelulusan | ❌ | ✅ | ✅ |
| Luluskan penilaian | ❌ | ❌ | ✅ |
| Kunci tempoh bulanan | ❌ | ❌ | ✅ |
| Buka semula tempoh yang dikunci | ❌ | ❌ | ✅ |
| Nilai KPI team | ❌ | ✅ ³ | ✅ |
| Tanda bonus sebagai disahkan/dibayar | ❌ | ❌ | ✅ |
| Ubah target, wajaran, kadar bonus | ❌ | ❌ | ✅ |
| Ubah senarai kriteria penilaian | ❌ | ❌ | ✅ |
| Lihat audit trail | ❌ | ✅ ⁴ | ✅ |
| Eksport laporan | ❌ | ✅ | ✅ |

¹ Kandungan leaderboard yang dilihat staf boleh dikonfigurasi — lihat `05 §K7`.
² Kecuali jika mod *self-entry* dihidupkan — lihat `05 §K3`.
³ Bergantung pada siapa yang ditetapkan sebagai penilai team — lihat `05 §K9`.
⁴ Supervisor nampak jejak audit staf di bawah seliaannya sahaja.

**Peraturan yang tidak boleh dilanggar**

- Staf tidak boleh mengubah markah sendiri, dalam apa jua keadaan.
- Supervisor tidak boleh meluluskan penilaian yang dia sendiri buat. Penilai dan
  pelulus mesti orang berbeza; sistem menguatkuasakan ini.
- Rekod berstatus `Approved` atau `Locked` tidak boleh diubah oleh sesiapa. Untuk
  membetulkannya, Admin mesti `Reopen` dahulu, dan tindakan itu direkod.

## 3. Modul

### M1 — Profil staf

Senarai staf dengan nama, ID pekerja, peranan, tarikh mula, tarikh tamat
(jika berhenti), status aktif, dan Supervisor yang menyelia.

Staf yang berhenti **tidak dipadam** — dinyahaktifkan sahaja, supaya rekod
prestasi dan bonus lampau kekal utuh dan boleh diaudit. Staf tidak aktif
dikecualikan daripada leaderboard semasa tetapi kekal dalam laporan sejarah.

### M2 — Kemasukan jualan harian

Satu rekod per staf per tarikh:

| Medan | Jenis | Nota |
|---|---|---|
| Tarikh | tarikh | Tidak boleh tarikh hadapan |
| Staf | rujukan | Mesti aktif pada tarikh itu |
| Jualan (RM) | perpuluhan | Boleh sifar; negatif hanya melalui rekod pelarasan |
| Kuantiti produk fokus | integer | Kategori produk yang dijejak berasingan, contoh unit tertentu |
| Catatan | teks | Pilihan |

Rekod harian adalah **satu-satunya sumber kebenaran**. Angka mingguan dan bulanan
tidak pernah dimasukkan secara berasingan — semuanya dijumlahkan daripada rekod
harian. Ini menghapuskan kelas pepijat di mana rumusan dan butiran tidak sepadan.

Kekangan unik pada (staf, tarikh) menghalang kemasukan berganda. Pembetulan
dibuat dengan mengedit rekod sedia ada — setiap edit direkod dalam audit trail
dengan nilai lama dan nilai baharu.

Pemulangan atau jualan dibatalkan dikendalikan sebagai **rekod pelarasan**
bertarikh, bukan dengan mengedit rekod asal. Lihat `05 §K4`.

### M3 — Rumusan mingguan & bulanan

Dijana atas permintaan daripada rekod harian, tidak disimpan sebagai data berasingan
(kecuali sebagai cache prestasi — lihat `02 §8`).

- **Mingguan** — jumlah jualan, jumlah produk fokus, ranking, kemajuan berbanding
  target mingguan jika ditetapkan. Sempadan minggu perlu diputuskan (`05 §K5`).
- **Bulanan** — jumlah jualan, jumlah produk fokus, peratus target, markah KPI
  jualan, kedudukan. Dipaparkan sebagai *month-to-date* sepanjang bulan berjalan
  dan menjadi muktamad apabila tempoh dikunci.

### M4 — Penilaian KPI individu

Setiap tempoh bulanan, setiap staf aktif mendapat satu borang penilaian.

**Komponen jualan (40%)** diisi automatik daripada M2. Supervisor tidak boleh
menaipnya — ini menghalang markah jualan daripada dipandu perasaan.

**Komponen prestasi (60%)** dinilai Supervisor: 25 item merentas lima kategori,
setiap item pada skala tiga gred.

| Skor | Tahap | Maksud |
|:--:|---|---|
| 0 | Tidak memuaskan | Tidak dilaksanakan, atau kerap tidak mengikut standard |
| 1 | Memuaskan | Melaksanakan kerja seperti yang sepatutnya |
| 2 | Cemerlang | Konsisten, proaktif, tidak perlu diingatkan |

Setiap kategori mengandungi 5 item, jadi maksimum mentah setiap kategori ialah 10.

Skor **0 mewajibkan catatan**. Ini melindungi kedua-dua pihak: staf tahu sebab
markah rendah, dan syarikat ada rekod bertulis jika keputusan bonus dipertikaikan.
Sistem menolak penghantaran jika ada skor 0 tanpa catatan.

Skor 2 juga digalakkan disertakan catatan tetapi tidak diwajibkan (`05 §K10`).

### M5 — Penilaian KPI team

Struktur sama, satu penilaian untuk seluruh team setiap tempoh. Komponen jualan
diukur terhadap target team; lima kategori lain dinilai untuk team secara
keseluruhan, bukan purata markah individu.

Keputusan team menghasilkan **bonus team per staf** — setiap staf yang layak
menerima jumlah yang sama.

### M6 — Bonus & insentif

Tiga ganjaran berasingan, dikira automatik, dijumlahkan menjadi satu jumlah:

1. **Bonus KPI individu** — berdasarkan jumlah KPI individu.
2. **Insentif jualan tinggi** — berdasarkan jumlah jualan bulanan, berasingan
   sepenuhnya daripada markah KPI. Ini yang memberi ganjaran kepada jualan
   melebihi target, memandangkan komponen KPI jualan berhenti pada 40.
3. **Bonus KPI team** — berdasarkan markah KPI team, sama rata untuk staf layak.

Semua kadar dan sempadan gred adalah tetapan, bukan nilai dalam kod.

Pengiraan **bukan kebenaran untuk membayar**. Ganjaran mempunyai status
tersendiri: `Dikira → Disahkan → Dibayar`. Dokumen asal menetapkan bahawa bonus
hanya dibayar selepas pengurusan menyemak rekod jualan, kehadiran, disiplin dan
SOP — jadi sistem mengasingkan angka daripada kelulusan pembayaran.

Kelayakan bonus untuk staf yang bekerja kurang daripada satu bulan penuh belum
ditetapkan — lihat `05 §K6`.

### M7 — Leaderboard & dashboard

| Paparan | Hari | Minggu | Bulan | Tahun |
|---|:--:|:--:|:--:|:--:|
| Jumlah jualan / top seller / % target | ✅ | ✅ | ✅ | ✅ |
| Produk fokus (kuantiti) | ✅ | ✅ | ✅ | ✅ |
| Ranking jualan (RM) | ✅ | ✅ | ✅ | ✅ |
| Ranking produk fokus | ✅ | ✅ | ✅ | ✅ |
| Ranking KPI | — | — | ✅ ¹ | ✅ ¹ |
| Profil staf: jualan, KPI, ranking, trend | ✅ | ✅ | ✅ | ✅ |
| Dashboard management: team, target, penilaian tertunggak | ✅ | ✅ | ✅ | ✅ |
| Carta trend | ✅ | ✅ | ✅ | ✅ |

¹ Ranking KPI hanya menggunakan penilaian yang **sudah diluluskan**. Markah draf
tidak pernah muncul pada leaderboard — ia akan membocorkan penilaian belum muktamad.

Seri dalam ranking dipaparkan sebagai kedudukan sama (dua orang di tempat ke-2,
orang seterusnya di tempat ke-4).

### M8 — Workflow & audit

**Status dan peralihan**

| Dari | Ke | Siapa | Syarat |
|---|---|---|---|
| — | `Draft` | Supervisor, Admin | Tempoh dibuka |
| `Draft` | `Submitted` | Supervisor, Admin | Semua 25 item diisi; setiap skor 0 ada catatan |
| `Submitted` | `Draft` | Admin | Dipulangkan untuk pembetulan, sebab diwajibkan |
| `Submitted` | `Approved` | Admin | Pelulus ≠ penilai |
| `Approved` | `Locked` | Admin | Penguncian di peringkat tempoh, bukan seorang demi seorang |
| `Locked` | `Reopened` | Admin | Sebab diwajibkan; jejak audit dicipta |
| `Reopened` | `Submitted` | Supervisor, Admin | Kitaran biasa disambung semula |

Peralihan lain ditolak. Peraturan ini dikuatkuasakan di lapisan pelayan, bukan
dengan menyembunyikan butang — antara muka yang menyembunyikan butang masih boleh
dipintas.

**Audit trail** merekod, untuk setiap perubahan: siapa, bila, entiti apa, medan
apa, nilai sebelum, nilai selepas, dan sebab jika diwajibkan. Rekod audit tidak
boleh diedit atau dipadam oleh mana-mana peranan, termasuk Admin. Tanpa jaminan
ini audit trail tidak bermakna.

### M9 — Laporan & eksport

Tapisan mengikut bulan, tahun, staf dan status. Laporan asas:

- Ringkasan KPI bulanan (semua staf, satu tempoh).
- Baucar bonus per staf — pecahan tiga komponen ganjaran.
- Trend jualan mengikut staf merentas tempoh.
- Penilaian tertunggak (apa yang menghalang penutupan bulan).
- Log audit mengikut tempoh atau staf.

Eksport Excel dan PDF diletakkan sebagai *should have*, bukan *must have*.

### M10 — Tetapan

Semuanya boleh dikonfigurasi tanpa menyentuh kod: target individu, target team,
target mingguan, wajaran komponen, senarai kriteria dengan teks tiga gredmya,
gred bonus, gred insentif, kadar bonus team, peraturan pembundaran, sempadan minggu.

**Tetapan berversi.** Apabila target atau kadar bonus berubah, tempoh lampau
mesti terus mengira menggunakan nilai yang berkuat kuasa ketika itu. Ini dicapai
dengan mengambil *snapshot* konfigurasi ke dalam tempoh apabila tempoh dibuka —
lihat `02 §3.4`. Tanpa ini, menaikkan target pada bulan Mac secara senyap menulis
semula KPI bulan Januari, dan bonus yang sudah dibayar tidak lagi sepadan dengan
sistem.

## 4. Notifikasi

Modul ini berkongsi saluran WhatsApp yang sudah digunakan produk untuk reminder:

| Peristiwa | Penerima |
|---|---|
| Tempoh bulanan dibuka untuk penilaian | Supervisor |
| Peringatan penilaian belum dihantar (H-3 sebelum tarikh tutup) | Supervisor |
| Penilaian dihantar untuk kelulusan | Admin |
| Penilaian dipulangkan untuk pembetulan | Supervisor |
| KPI diluluskan | Staf berkenaan |
| Tempoh dikunci, ganjaran sedia untuk semakan | Admin |
| Tempoh dibuka semula | Admin + Supervisor |

Notifikasi kepada staf hanya dihantar selepas kelulusan. Menghantar markah draf
kepada staf akan mencetuskan pertikaian tentang angka yang belum muktamad.

## 5. Perlindungan data peribadi

Modul ini menyimpan data prestasi dan ganjaran pekerja — data peribadi di bawah
Akta Perlindungan Data Peribadi 2010.

- **Had akses.** Staf melihat rekod sendiri sahaja. Supervisor melihat staf di
  bawah seliaannya. Ini kebenaran di peringkat pertanyaan pangkalan data, bukan
  penapisan di antara muka.
- **Tujuan.** Data dikumpul untuk pengurusan prestasi dan pengiraan ganjaran
  sahaja. Ia tidak digunakan untuk tujuan lain tanpa kebenaran.
- **Notis & kebenaran.** Staf perlu dimaklumkan secara bertulis apa yang direkod,
  siapa boleh melihatnya, dan berapa lama disimpan — biasanya melalui notis
  privasi pekerja, bukan melalui borang dalam aplikasi.
- **Tempoh simpanan.** Perlu ditetapkan (`05 §K13`). Rekod prestasi lazimnya
  disimpan sepanjang tempoh perkhidmatan campur satu tempoh selepas berhenti.
- **Ketepatan & akses.** Staf berhak melihat rekod sendiri dan meminta pembetulan.
  Paparan KPI peribadi memenuhi keperluan ini; permintaan pembetulan melalui
  proses `Reopen` yang direkod.
- **Keselamatan.** Kata laluan atau PIN mesti di-*hash*. Data sandaran disulitkan.
  Akses pangkalan data terhad kepada aplikasi.

## 6. Keperluan bukan fungsi

- **Zon waktu** — semua tarikh operasi dalam waktu Malaysia. Cap masa disimpan
  dalam UTC dan ditukar untuk paparan. Tarikh jualan ialah tarikh kalendar, bukan
  cap masa, supaya jualan lewat malam tidak tergelincir ke hari berikutnya.
- **Ketepatan wang** — semua nilai wang disimpan sebagai integer sen, atau
  perpuluhan tepat. Tidak sekali-kali nombor apungan. Wang yang dibundarkan secara
  tidak konsisten menghasilkan aduan bonus.
- **Serentak** — dua Supervisor mengedit penilaian yang sama perlu dikesan
  (kawalan versi optimistik), bukan senyap-senyap menimpa satu sama lain.
- **Prestasi** — leaderboard dan dashboard adalah pertanyaan paling kerap. Indeks
  dalam `02 §7` disusun untuknya; cache rumusan bulanan hanya jika diperlukan.
- **Sandaran** — sandaran harian automatik, disimpan luar pelayan, dengan pemulihan
  yang benar-benar pernah diuji.
