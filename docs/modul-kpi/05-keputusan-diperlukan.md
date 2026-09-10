# 05 — Keputusan Diperlukan

Empat belas perkara yang perlu dijawab sebelum coding bermula. Setiap satu
disertakan cadangan — jika cadangan itu diterima, tandakan sahaja dan teruskan.

Sepuluh daripadanya (K1–K6, K8, K11, K13, K14) mengubah skema pangkalan data atau
formula. Menjawabnya sekarang mengambil masa beberapa jam. Menjawabnya selepas
sistem dibina bermakna migrasi data dan pengiraan semula rekod lampau.

---

### K1 — Sempadan gred bonus

**Soalan.** Jadual asal menggunakan julat `90% – 100%`, `80% – 89%`, `70% – 79%`.
Markah sebenar tidak semestinya nombor bulat. Apa yang berlaku pada 89.5?

**Mengapa penting.** Perbezaannya bernilai RM100 kepada staf berkenaan, dan tanpa
peraturan bertulis dua orang yang mengira dengan tangan akan mendapat jawapan
berbeza. Ini punca pertikaian bonus yang paling mudah dielakkan.

**Cadangan.** Guna sempadan "lebih besar atau sama dengan": 90 ke atas, 80 hingga
bawah 90, 70 hingga bawah 80, selebihnya sifar. Sama hasilnya untuk nombor bulat,
dan menutup lompang. Terpakai juga pada jadual insentif jualan.

---

### K2 — Adakah produk fokus masuk dalam KPI?

**Soalan.** Kuantiti produk fokus (kategori produk yang dijejak berasingan)
direkod harian dan muncul pada leaderboard. Adakah ia mempengaruhi markah KPI,
atau rekod prestasi sahaja?

**Mengapa penting.** Jika ia masuk KPI, wajaran 40/15/15/10/10/10 perlu dibuka
semula dan jumlahnya tidak lagi 100.

**Cadangan.** Kekalkan sebagai rekod dan leaderboard sahaja, seperti dalam
cadangan asal. Ia sudah memberi kesan melalui nilai jualan. Mengiranya dua kali
akan memihak kepada satu kategori produk berbanding jualan keseluruhan.

---

### K3 — Siapa memasukkan jualan harian?

**Soalan.** Supervisor sahaja, atau staf memasukkan jualan sendiri?

**Mengapa penting.** Staf memasukkan jualan sendiri bermakna staf memberi kesan
kepada 40% markahnya sendiri. Itu memerlukan pengesahan, kalau tidak angka itu
tidak boleh dipercayai.

**Cadangan.** Supervisor memasukkan, sekurang-kurangnya pada mulanya. Jika
kemasukan sendiri diperlukan kemudian, tambah langkah pengesahan Supervisor
sebelum angka dikira ke dalam KPI.

---

### K4 — Pemulangan & pembatalan jualan

**Soalan.** Bagaimana pemulangan direkod, dan apa berlaku jika pemulangan berlaku
selepas bulan berkenaan dikunci?

**Mengapa penting.** Membenarkan pengeditan rekod jualan lampau bermakna KPI dan
bonus yang telah diluluskan boleh berubah selepas dibayar.

**Cadangan.** Rekod pelarasan bertarikh dengan sebab diwajibkan, tidak pernah
mengedit rekod asal. Pemulangan selepas penguncian direkod pada bulan semasa.
Bulan yang dikunci kekal seperti diluluskan.

---

### K5 — Sempadan minggu & target mingguan

**Soalan.** Minggu bermula hari apa? Bagaimana minggu yang merentas dua bulan
dikira? Adakah target mingguan wujud?

**Mengapa penting.** "Minggu 1–5" tidak bermakna tanpa definisi, dan bulan
mempunyai bilangan hari bekerja berbeza.

**Cadangan.** Minggu Isnin hingga Ahad. Rumusan mingguan mengikut minggu kalendar
dan boleh merentas sempadan bulan; rumusan bulanan sentiasa mengikut bulan
kalendar dan itulah yang mengira KPI. Target mingguan sebagai tetapan pilihan,
lalai dimatikan — bulan berbeza mempunyai hari operasi berbeza dan target
mingguan tetap akan menyesatkan.

---

### K6 — Staf baru, staf berhenti, cuti panjang

**Soalan.** Staf yang mula pada 15 haribulan — adakah target penuh terpakai?
Staf bercuti bersalin sebulan — adakah KPI dinilai? Adakah bonus dibayar?

**Mengapa penting.** Target penuh untuk separuh bulan bermakna markah jualan
mustahil dicapai, yang menjadikan keseluruhan KPI tidak adil dan sistem hilang
kredibiliti pada bulan pertama seseorang bekerja.

**Cadangan.** Pro-rata target mengikut hari bekerja dalam tempoh. Untuk cuti
panjang, kecualikan staf daripada tempoh itu sepenuhnya — tiada KPI, tiada bonus,
tiada leaderboard — dan bukan memberi mereka markah sifar. Perlu keputusan
bertulis kerana ia melibatkan bayaran.

---

### K7 — Apa yang staf nampak pada leaderboard

**Soalan.** Adakah staf melihat angka jualan rakan sekerja? Markah KPI rakan
sekerja? Nama penuh atau ranking sahaja?

**Mengapa penting.** Ketelusan mendorong persaingan sihat, tetapi markah KPI
adalah maklum balas prestasi peribadi. Mendedahkannya kepada semua orang mengubah
sifat penilaian itu, dan Supervisor akan mula memberi markah lebih lembut kerana
tahu markah itu akan dilihat umum.

**Cadangan.** Staf melihat ranking jualan dan produk fokus dengan nama — itu
bahagian pertandingan. Markah KPI penuh hanya kepada pemiliknya, Supervisor dan
Admin. Boleh dikonfigurasi jika management mahu sebaliknya.

---

### K8 — Siapa layak menerima bonus team?

**Soalan.** Dokumen asal menulis "jika 5 orang staf layak". Apakah syarat kelayakan?

**Mengapa penting.** Tanpa syarat, staf yang menyertai pada minggu terakhir bulan
menerima jumlah yang sama seperti staf yang bekerja sepanjang bulan.

**Cadangan.** Layak jika staf bekerja sekurang-kurangnya satu ambang tempoh yang
ditetapkan dalam bulan itu (contoh 15 hari bekerja) **dan** KPI individunya telah
diluluskan. Ambang ditetapkan sebagai tetapan.

---

### K9 — Siapa menilai dan meluluskan KPI team?

**Soalan.** KPI individu dinilai Supervisor dan diluluskan Admin. KPI team dinilai
oleh siapa?

**Mengapa penting.** Jika Supervisor menilai team yang termasuk dirinya, itu
penilaian diri yang memberi kesan kepada bonusnya sendiri.

**Cadangan.** Management menilai KPI team; Admin meluluskan. Jika Supervisor yang
menilai, sistem mesti mengecualikan Supervisor daripada penerima bonus team, atau
kelulusan mesti datang dari peringkat lebih tinggi.

---

### K10 — Catatan wajib

**Soalan.** Catatan diwajibkan untuk skor 0. Adakah skor 2 juga perlu diwajibkan
catatan?

**Mengapa penting.** Mewajibkan catatan untuk skor 2 menghasilkan bukti bertulis
untuk markah tinggi, tetapi menambah 25 kotak teks kepada borang yang perlu diisi
setiap bulan untuk setiap staf. Beban itu akan menyebabkan catatan salin-tampal.

**Cadangan.** Wajib untuk 0 sahaja. Galakkan untuk 2 tanpa menguatkuasakan.

---

### K11 — Jualan dikongsi dua staf

**Soalan.** Seorang staf memulakan jualan, seorang lagi menutupnya. Siapa mendapat
kredit?

**Mengapa penting.** Ini berlaku setiap hari di kedai. Tanpa peraturan, ia menjadi
sumber pertikaian antara staf yang sistem tidak dapat selesaikan.

**Cadangan.** Satu jualan, satu staf — peraturan mudah adalah lebih baik daripada
peraturan adil yang tidak dipatuhi. Jika pembahagian benar-benar diperlukan,
`daily_sales` perlu direka semula sebagai transaksi individu dengan pembahagian
peratus, yang jauh lebih besar. Putuskan sekarang, bukan selepas dibina.

---

### K12 — Kalendar bulanan

**Soalan.** Bila penilaian dibuka, bila tarikh akhir Supervisor menghantar, bila
Admin meluluskan, bila tempoh dikunci?

**Mengapa penting.** Tanpa tarikh, penilaian tidak siap dan bulan tidak pernah
ditutup. Peringatan automatik memerlukan tarikh untuk dikira.

**Cadangan.** Tempoh dibuka pada hari pertama bulan berikutnya; Supervisor
menghantar dalam masa 5 hari bekerja; Admin meluluskan dalam masa 3 hari bekerja
selepas itu; tempoh dikunci selepas kelulusan selesai. Semua boleh dikonfigurasi.

---

### K13 — Simpanan data & notis privasi

**Soalan.** Berapa lama rekod prestasi dan bonus disimpan selepas staf berhenti?
Adakah notis privasi pekerja sedia ada meliputi data ini?

**Mengapa penting.** Ini data peribadi di bawah Akta Perlindungan Data Peribadi
2010. Menyimpannya tanpa had tanpa tujuan yang dinyatakan adalah masalah
pematuhan, dan pekerja berhak tahu apa yang direkod tentang mereka.

**Cadangan.** Simpan sepanjang tempoh perkhidmatan campur satu tempoh selepas
berhenti, ditetapkan sebagai tetapan, kemudian dibuang atau dinyahkenal pasti.
Notis privasi pekerja perlu disemak untuk memasukkan data prestasi, ganjaran dan
jualan sebelum sistem digunakan.

---

### K14 — Seni bina & model harga

**Soalan.** Pilihan A (kekal atas Sheets), B (aplikasi berpusat) atau C (hibrid)?
Dan jika berpusat, bagaimana kos berulang ditampung?

**Mengapa penting.** Ini keputusan yang menentukan segala-galanya di bawahnya.
Menukarnya selepas Fasa 1 bermakna membina semula.

**Cadangan.** Pilihan B, dijual sebagai add-on berasingan daripada pakej SOP
sedia ada. Rasional penuh dalam `04 §2` dan `04 §6`.

---

## Rekod keputusan

| # | Perkara | Cadangan | Keputusan | Catatan |
|:--:|---|---|---|---|
| K1 | Sempadan gred bonus | Sempadan "≥" | | |
| K2 | Produk fokus dalam KPI | Rekod sahaja | | |
| K3 | Siapa key-in jualan | Supervisor | | |
| K4 | Pemulangan | Rekod pelarasan | | |
| K5 | Sempadan minggu | Isnin–Ahad, target mingguan pilihan | | |
| K6 | Staf separa bulan | Target pro-rata | | |
| K7 | Keterlihatan leaderboard | Jualan terbuka, KPI tertutup | | |
| K8 | Kelayakan bonus team | Ambang hari + KPI diluluskan | | |
| K9 | Penilai KPI team | Management menilai, Admin lulus | | |
| K10 | Catatan wajib | Skor 0 sahaja | | |
| K11 | Jualan dikongsi | Satu jualan satu staf | | |
| K12 | Kalendar bulanan | 5 hari + 3 hari | | |
| K13 | Simpanan data | Perkhidmatan + satu tempoh | | |
| K14 | Seni bina & harga | Pilihan B, add-on berasingan | | |
