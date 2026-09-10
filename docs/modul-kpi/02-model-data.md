# 02 — Model Data

Skema ditulis untuk pangkalan data hubungan (MySQL 8 / MariaDB / PostgreSQL).
Jika seni bina Google Sheets dipilih (`04 §2`), struktur logik yang sama kekal
terpakai — setiap jadual menjadi satu helaian — tetapi kekangan yang ditanda
**wajib** di bawah tidak boleh dikuatkuasakan oleh Sheets, dan itulah punca utama
mengapa Sheets tidak disyorkan untuk modul ini.

## 1. Gambaran keseluruhan

```
businesses ──┬── staff ──┬── daily_sales
             │           ├── kpi_assessments ── kpi_assessment_items ── kpi_criteria
             │           └── rewards
             ├── kpi_periods ──┬── team_assessments ── team_assessment_items
             │                 └── (snapshot konfigurasi)
             ├── kpi_criteria
             ├── settings
             └── audit_logs
```

`businesses` hadir supaya satu pemasangan boleh melayan lebih daripada satu klien.
Jika setiap klien mendapat pemasangan berasingan, jadual ini boleh digugurkan —
tetapi menambahnya kemudian bermakna memindahkan setiap jadual lain, jadi lebih
murah dimasukkan sekarang.

## 2. Pengguna & staf

### `users`

Akaun log masuk. Kekal berasingan daripada `staff` kerana Admin mungkin bukan staf
jualan, dan staf yang berhenti kehilangan akses tetapi rekodnya kekal.

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `name` | varchar | |
| `phone` | varchar | Untuk notifikasi WhatsApp |
| `email` | varchar nullable | |
| `pin_hash` / `password_hash` | varchar | **Wajib di-hash** |
| `role` | enum | `admin`, `supervisor`, `staff` |
| `is_active` | boolean | |
| `last_login_at` | timestamp nullable | |

### `staff`

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `user_id` | bigint FK nullable | Staf mungkin belum ada akaun log masuk |
| `employee_code` | varchar | Unik dalam business |
| `name` | varchar | |
| `supervisor_id` | bigint FK nullable | Menunjuk ke `staff` |
| `joined_on` | date | |
| `left_on` | date nullable | |
| `is_active` | boolean | |

Staf **tidak pernah dipadam**. `left_on` + `is_active = false` mengekalkan sejarah.

## 3. Jualan

### 3.1 `daily_sales`

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `staff_id` | bigint FK | |
| `sold_on` | date | Tarikh kalendar, bukan cap masa |
| `amount_cents` | bigint | Integer sen — jangan guna float |
| `focus_qty` | int | Kuantiti produk fokus, lalai 0 |
| `note` | text nullable | |
| `source` | enum | `manual`, `import`, `pos` — bersedia untuk integrasi kemudian |
| `entered_by` | bigint FK → users | |
| `created_at` / `updated_at` | timestamp | |

**Wajib:** unik `(staff_id, sold_on)` — menghalang kemasukan berganda yang akan
menggandakan jualan bulanan seseorang dan bonusnya sekali.

**Wajib:** `sold_on` tidak boleh melebihi hari ini.
**Wajib:** kemasukan ditolak jika tempoh bulanan berkenaan berstatus `Locked`.

### 3.2 `sales_adjustments`

Pemulangan, pembatalan dan pembetulan selepas fakta.

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `staff_id` | bigint FK | |
| `adjusted_on` | date | Tarikh pelarasan diambil kira |
| `original_sale_date` | date nullable | Jualan asal yang dirujuk |
| `amount_cents` | bigint | Boleh negatif |
| `focus_qty` | int | Boleh negatif |
| `reason` | text | **Diwajibkan** |
| `entered_by` | bigint FK | |

Diasingkan daripada `daily_sales` supaya jualan kasar dan bersih kedua-duanya
kekal boleh dilihat. Mengedit rekod asal akan menyembunyikan hakikat bahawa
pemulangan pernah berlaku.

### 3.3 `kpi_periods`

Satu baris per business per bulan. Inilah objek yang dikunci.

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `year` | smallint | |
| `month` | tinyint | 1–12 |
| `status` | enum | `open`, `closing`, `locked`, `reopened` |
| `config_snapshot` | json | Lihat 3.4 |
| `locked_at` / `locked_by` | timestamp / FK nullable | |
| `reopened_at` / `reopened_by` / `reopen_reason` | | Diisi apabila dibuka semula |

**Wajib:** unik `(business_id, year, month)`.

### 3.4 Snapshot konfigurasi

Apabila tempoh dibuka, konfigurasi yang berkuat kuasa disalin ke dalam
`config_snapshot`: target individu, target team, wajaran komponen, gred bonus,
gred insentif, kadar bonus team, peraturan pembundaran, dan versi senarai kriteria.

Semua pengiraan untuk tempoh itu membaca snapshot, bukan tetapan semasa.

Ini satu-satunya cara memastikan bulan yang sudah dibayar terus mengira nombor
yang sama tahun depan. Tanpa snapshot, menaikkan target daripada satu nilai ke
nilai lain akan menulis semula setiap KPI lampau, dan sistem tidak lagi bersetuju
dengan bonus yang benar-benar dibayar.

## 4. Penilaian KPI

### `kpi_criteria`

25 item penilaian, dengan teks tiga gred untuk setiap satu.

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `scope` | enum | `individual`, `team` |
| `category` | varchar | Contoh: khidmat pelanggan, operasi, disiplin |
| `category_weight` | decimal(5,2) | Peratus, contoh 15.00 |
| `label` | varchar | Nama item |
| `desc_0` / `desc_1` / `desc_2` | text | Maksud setiap gred — dipaparkan kepada penilai |
| `sort_order` | int | |
| `version` | int | Dinaikkan apabila senarai berubah |
| `is_active` | boolean | |

Kriteria **tidak diedit di tempat** setelah digunakan dalam tempoh yang dikunci.
Mengubah teks item selepas penilaian bermakna markah lama merujuk soalan yang
tidak lagi wujud. Perubahan mencipta versi baharu; versi lama kekal untuk sejarah.

### `kpi_assessments`

Satu baris per staf per tempoh.

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `period_id` | bigint FK | |
| `staff_id` | bigint FK | |
| `status` | enum | `draft`, `submitted`, `approved`, `locked`, `reopened` |
| `sales_amount_cents` | bigint | Dibekukan ketika dihantar |
| `sales_score` | decimal(5,2) | Komponen 40%, dikira |
| `performance_score` | decimal(5,2) | Komponen 60%, dikira |
| `total_score` | decimal(5,2) | Jumlah |
| `evaluated_by` | bigint FK nullable | |
| `submitted_at` / `approved_by` / `approved_at` | | |
| `returned_reason` | text nullable | |
| `version` | int | Kawalan serentak optimistik |

**Wajib:** unik `(period_id, staff_id)`.
**Wajib:** `approved_by ≠ evaluated_by`.
**Wajib:** tiada kemas kini apabila status `approved` atau `locked`.

Markah disimpan, bukan dikira semula ketika dibaca. Pengiraan langsung nampak
kemas sehingga kriteria berubah dan KPI tahun lalu senyap-senyap berbeza.

### `kpi_assessment_items`

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `assessment_id` | bigint FK | |
| `criteria_id` | bigint FK | |
| `score` | tinyint | 0, 1 atau 2 sahaja |
| `note` | text nullable | **Wajib apabila `score = 0`** |

**Wajib:** unik `(assessment_id, criteria_id)`.
**Wajib:** `score` antara 0 dan 2.

### `team_assessments` & `team_assessment_items`

Struktur sama, tetapi satu baris per tempoh dan bukan per staf. Menyimpan jualan
team, markah komponen jualan, markah prestasi team dan jumlah, dengan medan
workflow yang sama.

## 5. Ganjaran

### `rewards`

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `period_id` | bigint FK | |
| `staff_id` | bigint FK | |
| `kpi_total` | decimal(5,2) | Disalin daripada penilaian |
| `individual_bonus_cents` | bigint | |
| `high_sales_incentive_cents` | bigint | |
| `team_bonus_cents` | bigint | |
| `total_cents` | bigint | Jumlah ketiga-tiganya |
| `calc_snapshot` | json | Gred yang digunakan dan nilai yang dipadankan |
| `status` | enum | `calculated`, `verified`, `paid`, `withheld` |
| `verified_by` / `verified_at` | | |
| `paid_at` | timestamp nullable | |
| `withheld_reason` | text nullable | |

**Wajib:** unik `(period_id, staff_id)`.

`calc_snapshot` menyimpan sebab bagi setiap angka — gred mana yang dipadankan dan
nilai input apa. Apabila seorang staf bertanya mengapa bonusnya berkurang RM100
berbanding bulan lepas, jawapannya perlu boleh ditunjuk, bukan dikira semula
daripada ingatan.

## 6. Audit

### `audit_logs`

| Lajur | Jenis | Nota |
|---|---|---|
| `id` | bigint PK | |
| `business_id` | bigint FK | |
| `actor_id` | bigint FK → users | |
| `action` | varchar | `created`, `updated`, `submitted`, `approved`, `locked`, `reopened`, `deleted` |
| `auditable_type` / `auditable_id` | varchar / bigint | Rujukan polimorfik |
| `changes` | json | Nilai sebelum dan selepas, per medan |
| `reason` | text nullable | Diwajibkan untuk pemulangan, buka semula, penahanan bonus |
| `ip_address` / `user_agent` | varchar nullable | |
| `created_at` | timestamp | |

**Wajib:** tiada laluan kemas kini atau padam untuk jadual ini — sisip sahaja,
termasuk untuk Admin. Audit trail yang boleh disunting oleh orang yang diaudit
tidak membuktikan apa-apa.

Indeks pada `(auditable_type, auditable_id)` dan `(business_id, created_at)`.

## 7. Indeks

| Jadual | Indeks | Untuk |
|---|---|---|
| `daily_sales` | unik `(staff_id, sold_on)` | Kekangan + carian |
| `daily_sales` | `(business_id, sold_on)` | Jumlah harian & leaderboard |
| `daily_sales` | `(staff_id, sold_on)` julat | Jumlah bulanan per staf |
| `kpi_assessments` | unik `(period_id, staff_id)` | |
| `kpi_assessments` | `(period_id, status)` | Papan pemuka penilaian tertunggak |
| `kpi_assessment_items` | unik `(assessment_id, criteria_id)` | |
| `rewards` | unik `(period_id, staff_id)` | |
| `audit_logs` | `(auditable_type, auditable_id)` | Sejarah satu rekod |

## 8. Cache (hanya jika diperlukan)

Rumusan bulanan boleh dikira terus daripada `daily_sales` dengan `SUM` bertapis.
Pada saiz team yang dijangka, ini pantas dan sentiasa tepat.

Tambah jadual rumusan yang disimpan hanya apabila pengukuran menunjukkan
keperluan sebenar. Jika ditambah, ia mesti dijana semula secara automatik apabila
mana-mana rekod harian berubah — rumusan cache yang tidak segerak dengan butiran
adalah tepat jenis pepijat yang direka bentuk ini untuk dielakkan.
