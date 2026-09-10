# Modul KPI, Prestasi & Bonus Staf — Cadangan Sistem

Dokumen kerja untuk menambah **modul KPI** ke dalam produk DYNO SOP Staf.

Status: **Fasa 1 sudah dibina** — aplikasi Laravel dalam [`../../kpi/`](../../kpi/),
70 ujian hijau. Keputusan K1–K14 di bawah dilaksanakan mengikut cadangan dalam
`05`; semuanya boleh diubah dari skrin Tetapan tanpa menyentuh kod. Keputusan
rasmi management masih diperlukan — jika mana-mana berbeza, tukar tetapan, bukan
kod.

Modul ini menambah pada apa yang SOPSTAF buat sekarang (SOP + reminder + tick):
rekod jualan harian, penilaian prestasi berwajaran, pengiraan bonus, leaderboard,
serta workflow kelulusan dengan audit trail.

## Sumber

Reka bentuk ini diterjemah daripada dua dokumen klien pilot:

1. Dokumen SOP asal — struktur KPI, formula, jadual bonus & insentif, contoh pengiraan.
2. Hamparan cadangan untuk kelulusan management — skala penilaian 0/1/2, modul
   daily→weekly→monthly, leaderboard, workflow, senarai requirement developer.

Fail sumber **tidak** disimpan dalam repo ini (repo ini public — lihat *Nota
kerahsiaan* di bawah). Semua nombor klien dalam dokumen ini dilayan sebagai
**nilai lalai yang boleh dikonfigurasi**, bukan nilai tetap dalam kod.

## Susunan dokumen

| Fail | Kandungan |
|---|---|
| [`01-spesifikasi-fungsi.md`](01-spesifikasi-fungsi.md) | Skop, peranan & kebenaran, modul M1–M10, workflow, notifikasi, PDPA |
| [`02-model-data.md`](02-model-data.md) | Skema pangkalan data penuh, indeks, constraint, keputusan reka bentuk |
| [`03-pengiraan.md`](03-pengiraan.md) | Semua formula, peraturan pembundaran, kes tepi, contoh pengiraan disemak |
| [`04-pelan-pembinaan.md`](04-pelan-pembinaan.md) | Pilihan seni bina, cadangan stack, fasa pembinaan, ujian, deploy |
| [`05-keputusan-diperlukan.md`](05-keputusan-diperlukan.md) | 14 keputusan yang perlu dibuat sebelum coding bermula |

Baca ikut susunan. `05` adalah yang paling mendesak — sepuluh daripada keputusan
itu mengubah skema pangkalan data, jadi lebih murah dijawab sekarang daripada
selepas dibina.

## Ringkasan cadangan

**Struktur KPI Individu (jumlah 100%)**

| Komponen | Wajaran | Cara dinilai |
|---|---|---|
| Pencapaian jualan individu | 40% | Automatik daripada rekod jualan, had maksimum 40 |
| Khidmat pelanggan | 15% | 5 item × skala 0/1/2 |
| Operasi kedai & pengurusan barang | 15% | 5 item × skala 0/1/2 |
| Disiplin & kehadiran | 10% | 5 item × skala 0/1/2 |
| Pematuhan SOP | 10% | 5 item × skala 0/1/2 |
| Kerjasama team & sikap kerja | 10% | 5 item × skala 0/1/2 |

**KPI Team** mengikut struktur yang sama, dengan komponen jualan diukur terhadap
target team dan bakinya dinilai sebagai satu kumpulan.

**Ganjaran** terdiri daripada tiga bahagian berasingan yang dijumlahkan:
bonus KPI individu + insentif jualan tinggi + bonus KPI team.

**Workflow** — `Draft → Submitted → Approved → Locked`, dengan `Reopened` yang
hanya boleh dibuka oleh Admin dan sentiasa meninggalkan jejak audit.

## Apa yang sudah dibina (Fasa 1)

Log masuk tiga peranan · profil staf · kemasukan jualan harian dan rekod
pelarasan · rumusan bulanan dengan markah jualan automatik · borang penilaian 25
item dengan catatan wajib pada skor 0 · workflow penuh dengan penguncian tempoh ·
pengiraan bonus, insentif dan bonus team dengan snapshot konfigurasi ·
leaderboard · papan pemuka staf dan management · audit trail sisip sahaja ·
skrin tetapan.

Arahan menjalankannya ada dalam [`../../kpi/CLAUDE.md`](../../kpi/CLAUDE.md).

## Tiga perkara yang perlu diputuskan awal

1. **Seni bina.** SOPSTAF hari ini duduk atas akaun Google klien (Apps Script +
   Sheets). Modul KPI mengira wang dan perlu rekod yang tidak boleh diubah
   selepas dikunci — Sheets lemah untuk itu. Lihat `04` untuk perbandingan penuh
   dan cadangan.
2. **Sempadan gred bonus.** Jadual asal menggunakan julat `80% – 89%`, yang
   meninggalkan lompang antara 89 dan 90. Skor sebenar boleh mendarat dalam
   lompang itu. Lihat `03 §4`.
3. **Kesan pada harga.** Model RM199/RM99 sekarang berpaksikan kos hosting sifar.
   Backend berpusat menukar andaian itu. Lihat `04 §6`.

## Nota kerahsiaan

Repo ini **public**. Dokumen di sini ditulis generik dengan sengaja — tiada nama
klien, tiada angka yang dikaitkan dengan mana-mana syarikat tertentu. Versi
khusus klien (nama syarikat, struktur bonus sebenar, senarai staf) patut disimpan
di tempat persendirian, bukan di sini.
