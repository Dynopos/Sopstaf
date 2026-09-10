<?php

/**
 * The assessment criteria, with the wording for each of the three grades.
 *
 * Text comes from the client's approved KPI proposal. Nothing here names the
 * business or carries a figure - the business name, targets and bonus rates all
 * live in settings, seeded from .env.
 */
return [
    'individual' => [
        [
            'key' => 'khidmat',
            'label' => 'Khidmat Pelanggan',
            'weight' => 15.00,
            'items' => [
                [
                    'Sambutan dan layanan kepada pelanggan',
                    'Tidak menyambut atau mengabaikan pelanggan',
                    'Menyambut dan melayan pelanggan dengan baik',
                    'Sentiasa mesra, peka dan melayan pelanggan sehingga selesai',
                ],
                [
                    'Pengetahuan tentang produk dan harga',
                    'Tidak tahu produk/harga asas dan kerap perlu bertanya',
                    'Tahu produk, harga dan maklumat asas',
                    'Sangat memahami produk dan boleh menerangkan dengan yakin',
                ],
                [
                    'Membantu pelanggan membuat pilihan',
                    'Tidak bertanya keperluan atau kurang membantu',
                    'Bertanya keperluan dan membantu pelanggan memilih',
                    'Aktif memahami keperluan dan memberi pilihan yang sesuai',
                ],
                [
                    'Jualan tambahan dan jualan silang',
                    'Tidak cuba mencadangkan produk tambahan',
                    'Mencadangkan produk tambahan apabila sesuai',
                    'Konsisten membuat cadangan tambahan secara profesional',
                ],
                [
                    'Sikap dan komunikasi ketika melayan pelanggan',
                    'Kurang sopan atau tidak profesional',
                    'Sopan dan berkomunikasi dengan baik',
                    'Sentiasa sopan, sabar dan profesional termasuk ketika menangani aduan',
                ],
            ],
        ],
        [
            'key' => 'operasi',
            'label' => 'Operasi Kedai & Pengurusan Barang',
            'weight' => 15.00,
            'items' => [
                [
                    'Susunan dan paparan produk',
                    'Produk kerap tidak kemas atau salah tempat',
                    'Produk disusun dan dipaparkan dengan kemas',
                    'Sentiasa memastikan susunan/display kemas tanpa perlu diingatkan',
                ],
                [
                    'Kebersihan dan kekemasan kedai',
                    'Kawasan kotor atau laluan tidak teratur',
                    'Menjaga kawasan jualan bersih dan kemas',
                    'Sentiasa peka dan terus mengemas apabila diperlukan',
                ],
                [
                    'Tanda harga dan maklumat produk',
                    'Tag/harga kerap tiada atau tidak lengkap',
                    'Memastikan tag dan harga produk lengkap',
                    'Proaktif menyemak dan membetulkan tag/harga yang tidak lengkap',
                ],
                [
                    'Pengurusan dan penjagaan barang',
                    'Cuai menjaga barang atau tidak melaporkan kerosakan',
                    'Menjaga barang, stok dan melaporkan barang rosak',
                    'Sangat teliti, mengamalkan FIFO dan proaktif mengurus stok/barang',
                ],
                [
                    'Penghantaran barang ke kenderaan pelanggan',
                    'Tidak membantu atau berlaku kesilapan barang/kuantiti',
                    'Membawa barang yang betul dan membantu hingga ke kenderaan',
                    'Semak barang dan kuantiti, susun dengan selamat dan pastikan tiada barang tertinggal',
                ],
            ],
        ],
        [
            'key' => 'disiplin',
            'label' => 'Disiplin & Kehadiran',
            'weight' => 10.00,
            'items' => [
                [
                    'Kehadiran dan pematuhan jadual kerja',
                    'Kerap tidak hadir atau tidak ikut jadual',
                    'Hadir dan bekerja mengikut jadual',
                    'Kehadiran sangat konsisten dan sentiasa ikut jadual',
                ],
                [
                    'Ketepatan waktu masuk',
                    'Kerap lewat',
                    'Biasanya hadir tepat pada waktu',
                    'Konsisten hadir tepat pada waktu',
                ],
                [
                    'Pematuhan waktu rehat',
                    'Kerap melebihi waktu rehat',
                    'Mengikut waktu rehat yang ditetapkan',
                    'Sangat konsisten menjaga waktu rehat',
                ],
                [
                    'Ponteng / meninggalkan tempat kerja',
                    'Ponteng atau meninggalkan tempat kerja tanpa kebenaran',
                    'Tidak ponteng dan meminta kebenaran jika perlu keluar',
                    'Sangat bertanggungjawab dan sentiasa memaklumkan dengan betul',
                ],
                [
                    'Arahan dan disiplin kerja',
                    'Kerap tidak ikut arahan atau perlu ditegur',
                    'Mengikut arahan dan menjaga disiplin',
                    'Sangat berdisiplin dan boleh dipercayai tanpa perlu dipantau',
                ],
            ],
        ],
        [
            'key' => 'sop',
            'label' => 'Pematuhan SOP',
            'weight' => 10.00,
            'items' => [
                [
                    'SOP Jualan & Customer Service',
                    'Kerap tidak ikut SOP',
                    'Mengikut SOP semasa melayan dan membuat jualan',
                    'Sentiasa mengikut SOP dengan baik tanpa perlu diingatkan',
                ],
                [
                    'SOP Operasi Kedai & Pengendalian Barang',
                    'Kerap tidak mengikut cara kerja yang ditetapkan',
                    'Mengendalikan operasi dan barang mengikut SOP',
                    'Sangat konsisten dan teliti mengikut SOP',
                ],
                [
                    'SOP Kebersihan & Keselamatan',
                    'Mengabaikan kebersihan atau keselamatan',
                    'Mematuhi SOP kebersihan dan keselamatan',
                    'Sentiasa peka dan terus bertindak apabila terdapat isu',
                ],
                [
                    'SOP Penggunaan Telefon',
                    'Kerap menggunakan telefon tidak mengikut SOP',
                    'Menggunakan telefon mengikut SOP',
                    'Sangat berdisiplin dan tidak perlu ditegur mengenai penggunaan telefon',
                ],
                [
                    'SOP Buka/Tutup Kedai & Arahan Kerja',
                    'Kerap tidak mengikut SOP atau arahan',
                    'Mengikut SOP dan arahan Supervisor/Pengurusan',
                    'Melaksanakan dengan betul dan konsisten tanpa perlu diingatkan',
                ],
            ],
        ],
        [
            'key' => 'kerjasama',
            'label' => 'Kerjasama Team & Sikap Kerja',
            'weight' => 10.00,
            'items' => [
                [
                    'Membantu rakan sekerja & ketika pelanggan ramai',
                    'Tidak mahu membantu walaupun diperlukan',
                    'Membantu apabila diperlukan',
                    'Terus membantu apabila nampak rakan atau pelanggan memerlukan bantuan',
                ],
                [
                    'Kesediaan membantu dalam tugasan',
                    'Memilih kerja atau enggan membantu bahagian lain',
                    'Melakukan tugas dan membantu apabila diminta',
                    'Tidak memilih kerja dan proaktif membantu bahagian lain',
                ],
                [
                    'Sikap dan hubungan di tempat kerja',
                    'Tidak menghormati rakan/Supervisor atau menimbulkan konflik',
                    'Menghormati rakan dan menerima teguran',
                    'Sangat profesional, mudah bekerjasama dan menerima teguran dengan baik',
                ],
                [
                    'Komunikasi & sikap proaktif',
                    'Komunikasi lemah dan hanya bekerja apabila diarahkan',
                    'Berkomunikasi dengan baik dan menjalankan tugas',
                    'Komunikasi baik serta proaktif melakukan kerja tanpa menunggu arahan',
                ],
                [
                    'Menjaga standard & nama baik syarikat',
                    'Tidak menjaga standard kerja atau imej syarikat',
                    'Menjaga standard dan nama baik syarikat',
                    'Sentiasa menunjukkan contoh kerja yang baik dan menjaga standard syarikat',
                ],
            ],
        ],
    ],

    // The team is scored as a whole, not averaged from individual scores -
    // averaging would measure something else, and would punish the team for one
    // weak member when the point is how the shop runs together.
    'team' => [
        [
            'key' => 'team_khidmat',
            'label' => 'Customer Service Team',
            'weight' => 15.00,
            'items' => [
                [
                    'Semua pelanggan dilayan',
                    'Ada pelanggan diabaikan atau tidak dilayan',
                    'Semua pelanggan dilayan',
                    'Semua pelanggan dilayan dengan pantas walaupun kedai sibuk',
                ],
                [
                    'Layanan mesra dan profesional',
                    'Layanan tidak konsisten atau kurang profesional',
                    'Layanan mesra dan profesional',
                    'Layanan mesra dan profesional secara konsisten oleh semua staf',
                ],
                [
                    'Penerangan produk tepat',
                    'Maklumat produk kerap salah atau tidak lengkap',
                    'Penerangan produk tepat',
                    'Penerangan produk tepat dan yakin daripada semua staf',
                ],
                [
                    'Pengendalian aduan pelanggan',
                    'Aduan tidak dikendalikan dengan baik',
                    'Aduan dikendalikan dengan baik',
                    'Aduan diselesaikan dengan profesional dan pelanggan berpuas hati',
                ],
                [
                    'Staf saling membantu ketika pelanggan ramai',
                    'Staf tidak membantu ketika kedai sibuk',
                    'Staf membantu ketika kedai sibuk',
                    'Staf bergerak sendiri membantu tanpa perlu diarahkan',
                ],
            ],
        ],
        [
            'key' => 'team_operasi',
            'label' => 'Operasi & Kekemasan Kedai',
            'weight' => 15.00,
            'items' => [
                [
                    'Kebersihan kedai',
                    'Kedai kerap tidak bersih',
                    'Kedai bersih',
                    'Kedai sentiasa bersih tanpa perlu diingatkan',
                ],
                [
                    'Susunan produk dan display',
                    'Produk dan display kerap tidak teratur',
                    'Produk tersusun dan display kemas',
                    'Susunan dan display sentiasa kemas dan menarik',
                ],
                [
                    'Kelengkapan harga dan tag',
                    'Harga/tag kerap tidak lengkap',
                    'Harga dan tag lengkap',
                    'Harga dan tag lengkap serta disemak berkala',
                ],
                [
                    'Pengendalian stok dan barang rosak',
                    'Stok tidak dikendalikan dan barang rosak tidak dilaporkan',
                    'Stok dikendalikan dan barang rosak dilaporkan',
                    'Stok diurus rapi dan isu dilaporkan awal',
                ],
                [
                    'Kesediaan kedai menerima pelanggan',
                    'Kedai kerap tidak bersedia ketika dibuka',
                    'Kedai bersedia menerima pelanggan',
                    'Kedai sentiasa bersedia sepenuhnya sebelum waktu buka',
                ],
            ],
        ],
        [
            'key' => 'team_barang',
            'label' => 'Pengurusan Barang & Kenderaan Pelanggan',
            'weight' => 10.00,
            'items' => [
                [
                    'Ketepatan barang yang dijual',
                    'Kerap berlaku kesilapan barang',
                    'Barang yang dijual adalah betul',
                    'Barang sentiasa disemak dan tepat',
                ],
                [
                    'Ketepatan kuantiti',
                    'Kerap berlaku kesilapan kuantiti',
                    'Kuantiti barang betul',
                    'Kuantiti sentiasa disemak dua kali',
                ],
                [
                    'Barang dibawa ke kenderaan pelanggan',
                    'Pelanggan kerap terpaksa bawa sendiri',
                    'Barang dibawa ke kenderaan pelanggan',
                    'Barang dibawa dan disusun dengan selamat setiap kali',
                ],
                [
                    'Tiada barang tertinggal',
                    'Pernah berlaku barang tertinggal',
                    'Tiada barang tertinggal',
                    'Semakan akhir dibuat sebelum pelanggan beredar',
                ],
                [
                    'Kawasan dikemas selepas barang diambil',
                    'Kawasan dibiarkan bersepah',
                    'Kawasan dikemas selepas barang diambil',
                    'Kawasan sentiasa dikemas serta-merta',
                ],
            ],
        ],
        [
            'key' => 'team_disiplin',
            'label' => 'Disiplin & Kehadiran Team',
            'weight' => 10.00,
            'items' => [
                [
                    'Kehadiran keseluruhan team',
                    'Kehadiran team lemah',
                    'Kehadiran team baik',
                    'Kehadiran team sangat konsisten',
                ],
                [
                    'Ketepatan waktu',
                    'Ramai staf kerap lewat',
                    'Staf hadir tepat pada waktu',
                    'Semua staf konsisten tepat pada waktu',
                ],
                [
                    'Pematuhan jadual kerja',
                    'Jadual kerap tidak dipatuhi',
                    'Jadual dipatuhi',
                    'Jadual dipatuhi sepenuhnya tanpa perlu dipantau',
                ],
                [
                    'Pematuhan waktu rehat',
                    'Waktu rehat kerap dilebihi',
                    'Waktu rehat dipatuhi',
                    'Waktu rehat dipatuhi secara konsisten oleh semua staf',
                ],
                [
                    'Mematuhi arahan Supervisor',
                    'Arahan kerap tidak diikuti',
                    'Arahan Supervisor diikuti',
                    'Arahan diikuti dengan cepat dan tepat',
                ],
            ],
        ],
        [
            'key' => 'team_kerjasama',
            'label' => 'Kerjasama & Pematuhan SOP',
            'weight' => 10.00,
            'items' => [
                [
                    'Saling membantu antara staf',
                    'Staf tidak saling membantu',
                    'Staf saling membantu',
                    'Staf saling membantu tanpa perlu diminta',
                ],
                [
                    'Pematuhan SOP secara keseluruhan',
                    'SOP kerap tidak dipatuhi',
                    'SOP dipatuhi',
                    'SOP dipatuhi sepenuhnya dan konsisten',
                ],
                [
                    'Komunikasi dalam team',
                    'Komunikasi lemah dan menimbulkan salah faham',
                    'Komunikasi dalam team baik',
                    'Komunikasi jelas dan proaktif dalam team',
                ],
                [
                    'Pengurusan konflik',
                    'Konflik kerap berlaku dan menjejaskan kerja',
                    'Tiada konflik yang menjejaskan kerja',
                    'Masalah diselesaikan secara profesional dan awal',
                ],
                [
                    'Menjaga standard kerja syarikat',
                    'Standard kerja tidak dijaga',
                    'Standard kerja syarikat dijaga',
                    'Standard kerja dijaga dan ditunjukkan sebagai contoh',
                ],
            ],
        ],
    ],
];
