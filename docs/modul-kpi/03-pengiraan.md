# 03 — Pengiraan

Semua formula di sini menggunakan nilai daripada `config_snapshot` tempoh
(`02 §3.4`), bukan tetapan semasa. Nama tetapan ditulis dalam `kod`.

## 1. KPI jualan individu — komponen 40%

```
markah_jualan = MIN( jualan_bersih ÷ target_individu × 40 , 40 )
```

- `jualan_bersih` = jumlah `daily_sales` untuk bulan itu **campur** semua
  `sales_adjustments` yang jatuh dalam bulan itu.
- `target_individu` — tetapan, contoh RM40,000 sebulan.
- Had 40 adalah keras. Jualan melebihi target tidak menghasilkan markah tambahan;
  ia diberi ganjaran melalui insentif jualan tinggi (§5).

| Jualan | Kiraan | Markah |
|---|---|---|
| RM40,000 | 40,000 ÷ 40,000 × 40 | 40.00 |
| RM36,000 | 36,000 ÷ 40,000 × 40 | 36.00 |
| RM32,000 | 32,000 ÷ 40,000 × 40 | 32.00 |
| RM20,000 | 20,000 ÷ 40,000 × 40 | 20.00 |
| RM60,000 | dihadkan | 40.00 |

**Perhatian pada paparan.** Jualan RM39,999 menghasilkan 39.999, yang dipaparkan
sebagai 40.00 jika dibundarkan kepada dua tempat perpuluhan — kelihatan seperti
target dicapai sedangkan tidak. Paparkan peratus pencapaian jualan berasingan
daripada markah, supaya perbezaan itu jelas.

## 2. Komponen prestasi — 60%

Setiap kategori mengandungi 5 item, setiap satu diberi 0, 1 atau 2. Maksimum
mentah setiap kategori ialah 10.

```
markah_kategori = ( jumlah_skor_item ÷ 10 ) × wajaran_kategori
markah_prestasi = jumlah semua markah_kategori
```

| Kategori | Wajaran | Maks mentah | Saiz langkah |
|---|:--:|:--:|:--:|
| Khidmat pelanggan | 15 | 10 | 1.5 |
| Operasi kedai & pengurusan barang | 15 | 10 | 1.5 |
| Disiplin & kehadiran | 10 | 10 | 1.0 |
| Pematuhan SOP | 10 | 10 | 1.0 |
| Kerjasama team & sikap kerja | 10 | 10 | 1.0 |
| **Jumlah** | **60** | | |

### 2.1 Kesan skala tiga gred terhadap nilai yang boleh dicapai

Ini perlu difahami sebelum jadual bonus dimuktamadkan.

Dokumen asal menilai kategori secara terus — contohnya "khidmat pelanggan 13/15".
Skala 0/1/2 pada 5 item tidak boleh menghasilkan 13. Untuk kategori berwajaran 15,
markah yang mungkin ialah:

```
0 · 1.5 · 3 · 4.5 · 6 · 7.5 · 9 · 10.5 · 12 · 13.5 · 15
```

13 dan 14 tiada dalam senarai itu. Nilai terdekat ialah 13.5.

Ini **bukan pepijat** — ia kesan semula jadi menukar penilaian bebas kepada skala
tiga gred, dan pertukaran itu memang disengajakan: ia menjadikan penilaian
Supervisor jauh lebih konsisten. Tetapi ia bermakna contoh dalam dokumen asal
tidak boleh dihasilkan semula item demi item, dan sesiapa yang membandingkan
sistem dengan dokumen akan perasan. Jumlah 90% yang sama masih boleh dicapai:

```
36.0  jualan            (RM36,000)
13.5  khidmat pelanggan (9/10)
13.5  operasi           (9/10)
 9.0  disiplin          (9/10)
 9.0  SOP               (9/10)
 9.0  kerjasama         (9/10)
────
90.0
```

Kesan penting: **jumlah KPI mendarat pada kelipatan 0.5** apabila digabung dengan
markah jualan yang berterusan. Nilai seperti 89.5 boleh berlaku — dan 89.5 jatuh
dalam lompang jadual bonus. Lihat §4.

## 3. Jumlah KPI individu

```
kpi_individu = markah_jualan + markah_prestasi        (maksimum 100.00)
```

Simpan pada dua tempat perpuluhan. Kira setiap komponen pada ketepatan penuh dan
bundarkan **sekali sahaja** pada peringkat jumlah — membundarkan setiap komponen
dahulu kemudian menjumlahkannya boleh menghasilkan angka berbeza, dan perbezaan
sekecil 0.01 boleh menukar gred bonus.

## 4. Bonus KPI individu

Nilai lalai yang dicadangkan:

| Jumlah KPI | Bonus |
|---|---|
| 90 ke atas | RM300 |
| 80 hingga bawah 90 | RM200 |
| 70 hingga bawah 80 | RM100 |
| Bawah 70 | RM0 |

### 4.1 Lompang dalam jadual asal

Jadual asal ditulis sebagai `90% – 100%`, `80% – 89%`, `70% – 79%`. Julat itu
mengandaikan markah adalah nombor bulat. Ia bukan — markah jualan berterusan dan
markah prestasi bergerak dalam langkah 0.5.

Skor 89.5 tidak termasuk dalam mana-mana julat. Begitu juga 79.5 dan 69.5. Tanpa
peraturan, dua orang yang mengira dengan tangan akan mendapat jawapan berbeza,
dan perbezaan itu bernilai RM100 kepada staf berkenaan.

**Cadangan:** gunakan sempadan "lebih besar atau sama dengan" seperti jadual di
atas. Ia sepadan dengan niat asal untuk semua nombor bulat, dan menutup lompang.

**Pilihan lain:** bundarkan jumlah KPI kepada nombor bulat terdekat dahulu, baru
padankan dengan julat asal. Ini lebih murah hati (89.5 menjadi 90 → RM300).
Sama ada satu boleh diterima, tetapi ia mesti **dipilih dan ditulis** — lihat
`05 §K1`.

## 5. Insentif jualan tinggi

Berdasarkan jualan bulanan sahaja. Tiada kaitan dengan markah KPI.

| Jualan bulanan | Pencapaian target | Insentif |
|---|---|---|
| RM70,000 ke atas | 175% ke atas | RM300 |
| RM60,000 hingga bawah RM70,000 | 150%–174% | RM200 |
| RM50,000 hingga bawah RM60,000 | 125%–149% | RM100 |
| Bawah RM50,000 | bawah 125% | RM0 |

Jadual asal menulis sempadan sebagai `RM50,000 – RM59,999`, yang meninggalkan
lompang untuk nilai seperti RM49,999.50. Sempadan "lebih besar atau sama dengan"
di atas menutupnya. Jika semua jualan sentiasa dalam ringgit bulat, kedua-duanya
sama sahaja — tetapi sen wujud dalam data sebenar.

Perhatikan bahawa staf pada RM49,999 dan staf pada RM40,000 kedua-duanya mendapat
markah jualan penuh 40 dan insentif RM0. Ini memang begitu reka bentuknya: had 40
memberi ganjaran mencapai target, dan insentif memberi ganjaran melampauinya
dengan jelas. Ia bukan kesilapan yang perlu "dibetulkan".

## 6. KPI team & bonus team

```
markah_jualan_team = MIN( jualan_team ÷ target_team × 40 , 40 )
kpi_team           = markah_jualan_team + markah_prestasi_team
```

`jualan_team` ialah jumlah jualan bersih semua staf dalam tempoh itu.
`target_team` ialah tetapan berasingan — **bukan** target individu didarab bilangan
staf. Jika ia dikira automatik, seorang staf berhenti akan menurunkan target team
secara senyap di tengah bulan, yang membuatkan sasaran bergerak sendiri.

Lima kategori prestasi team dinilai untuk team secara keseluruhan, bukan dipurata
daripada markah individu. Purata markah individu akan mengukur perkara lain — ia
akan menghukum team kerana seorang ahli yang lemah, sedangkan tujuan KPI team
ialah mengukur bagaimana kedai berjalan bersama.

Bonus team menggunakan gred yang sama seperti §4, dibayar **per staf yang layak**.
Definisi "layak" belum ditetapkan — lihat `05 §K8`.

## 7. Jumlah ganjaran

```
jumlah_ganjaran = bonus_kpi_individu + insentif_jualan_tinggi + bonus_kpi_team
```

Ketiga-tiganya bebas antara satu sama lain. Tiada satu pun mengurangkan yang lain.

## 8. Contoh disemak

Semua contoh mengandaikan KPI individu 90 dan KPI team 90, seperti dalam dokumen
asal. Angka di bawah telah disemak semula terhadap formula di atas.

| Jualan bulanan | Markah jualan | Bonus KPI | Insentif | Bonus team | Jumlah |
|---|:--:|---|---|---|---|
| RM36,000 | 36.00 | RM300 | RM0 | RM300 | **RM600** |
| RM50,000 | 40.00 (dihadkan) | RM300 | RM100 | RM300 | **RM700** |
| RM60,000 | 40.00 (dihadkan) | RM300 | RM200 | RM300 | **RM800** |
| RM70,000 | 40.00 (dihadkan) | RM300 | RM300 | RM300 | **RM900** |

Padan dengan jadual ringkasan dalam dokumen asal.

**Contoh KPI team.** Jualan team RM180,000 terhadap target RM200,000:

```
180,000 ÷ 200,000 × 40 = 36.00
markah prestasi team    = 54.00   (9/10 dalam setiap lima kategori)
kpi_team                = 90.00   → bonus team RM300 seorang
```

Lima staf layak menghasilkan RM1,500 bonus team keseluruhan.

## 9. Kes tepi yang perlu dikendalikan

| Keadaan | Kelakuan cadangan | Keputusan |
|---|---|---|
| Staf mula pertengahan bulan | Target pro-rata mengikut hari bekerja | `05 §K6` |
| Staf berhenti pertengahan bulan | Kira tempoh separa; kelayakan bonus perlu diputuskan | `05 §K6` |
| Staf cuti panjang (sakit, bersalin) | Kecualikan daripada leaderboard; layanan KPI perlu diputuskan | `05 §K6` |
| Penilaian tidak diluluskan sebelum penguncian | Tiada bonus dikira; disenaraikan sebagai tertunggak | — |
| Jualan direkod tetapi KPI tidak dinilai | Markah jualan wujud, jumlah KPI tidak lengkap, tiada bonus | — |
| Pemulangan selepas bulan dikunci | Rekod pelarasan pada bulan semasa, bukan menulis semula yang lampau | `05 §K4` |
| Dua staf berkongsi satu jualan | Perlu peraturan pengagihan | `05 §K11` |
| Jualan sifar sepanjang bulan | Markah jualan 0; komponen prestasi tetap dinilai | — |
| KPI melebihi 100 | Mustahil — kedua-dua komponen berhad | — |
| Team kurang daripada bilangan staf yang diandaikan | Target team ialah tetapan tetap, tidak berubah sendiri | §6 |

## 10. Nota pelaksanaan

- **Simpan sen sebagai integer.** Wang dalam nombor apungan akhirnya menghasilkan
  RM299.99 di suatu tempat, dan bonus adalah perkara yang orang semak.
- **Kira sekali, simpan hasilnya.** Markah dibekukan ketika penghantaran. Jangan
  kira semula ketika membaca — kriteria dan tetapan berubah, dan rekod lampau
  mesti kekal tetap.
- **Ujian unit adalah wajib untuk fail formula.** Setiap baris dalam §8 patut
  menjadi ujian. Ini bahagian sistem yang menyentuh gaji orang; ia patut mustahil
  untuk pecah secara senyap.
- **Tunjukkan kerja pengiraan pada antara muka.** Paparkan input dan gred yang
  dipadankan bersama setiap angka bonus. Ia mengurangkan pertikaian jauh lebih
  banyak daripada sebarang penjelasan selepas fakta.
