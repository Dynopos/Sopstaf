# DYNO SOP Staf — Landing Page

Landing page statik (satu fail HTML, tanpa build step) untuk **DYNO SOP Staf** —
sistem SOP & reminder tugasan automatik untuk staf bisnes kecil di Malaysia.

Bahasa: Bahasa Melayu (`lang="ms"`). Tiada dependency runtime, tiada font/CDN luar —
semua CSS & JS inline supaya first paint pantas dan Core Web Vitals kekal elok.

Domain: **https://sopstaf.dynopro.my**

## Struktur

```
index.html                  Landing page penuh (CSS + JS inline)
robots.txt                  Benarkan semua crawler + rujukan sitemap
sitemap.xml                 Satu URL
site.webmanifest            Metadata PWA + ikon

icons/icon-192.png          Logo DYNO — sumber, jangan tulis ganti
icons/icon-512.png          Logo DYNO — sumber, jangan tulis ganti
icons/apple-touch-icon.png  Dijana dari icon-512 (180px, atas latar jenama)
img/og.png                  Dijana — kad kongsi sosial 1200x630

tools/render.js             Jana semula apple-touch-icon + kad OG
tools/og.html               Sumber reka bentuk kad OG
tools/verify.js             Semakan SEO + ujian fungsi demo + screenshot
```

## Preview lokal

```bash
npx http-server . -p 8080     # buka http://localhost:8080
```

## Jana semula aset

`tools/render.js` mengambil `icons/icon-512.png` sebagai sumber. Ia **tidak** menulis
ganti fail logo — ia hanya menghasilkan `icons/apple-touch-icon.png` (dileper atas
latar jenama, sebab iOS jadikan ketelusan hitam) dan `img/og.png`.

```bash
NODE_PATH=$(npm root -g) node tools/render.js
```

Kalau logo bertukar: ganti `icons/icon-192.png` + `icons/icon-512.png`, jalankan
arahan di atas.

## Semakan sebelum push

```bash
NODE_PATH=$(npm root -g) node tools/verify.js
```

Menyemak 29 perkara: struktur SEO (satu `<h1>`, panjang title/description, canonical,
Open Graph, Twitter Card, JSON-LD, padanan soalan FAQ dengan schema), setiap `<img>`
ada `width`/`height`/`alt`, tiada rujukan aset yang rosak, tiada horizontal scroll pada
390px, dan demo interaktif betul-betul berfungsi (tukar staf, tick, tab Admin, reset).
Screenshot disimpan dalam `.build/` (tidak di-commit).

## Deploy — GitHub Pages

Halaman ini statik di root repo, jadi Pages boleh terus terbit dari branch.

**Penting:** custom domain perlu fail `CNAME` dalam branch yang diterbitkan. Cara paling
selamat ialah set di **Settings → Pages → Custom domain** (`sopstaf.dynopro.my`) —
GitHub akan cipta `CNAME` sendiri dan uruskan sijil HTTPS. Kalau fail itu tiada, tapak
akan terbit di `dynopos.github.io` sedangkan semua tag SEO menunjuk ke
`sopstaf.dynopro.my`; canonical yang tak padan dengan URL sebenar boleh jejaskan
pengindeksan.

DNS di pihak `dynopro.my`: satu rekod `CNAME` untuk `sopstaf` → `dynopos.github.io`.

## Status SEO

Sudah siap dalam kod:

- Satu `<h1>` mengandungi keyword utama (*sistem SOP staf*); hierarki `h2`/`h3` betul
- `<title>` 51 aksara, meta description 155 aksara, canonical mutlak
- Open Graph + Twitter Card lengkap dengan imej 1200x630
- JSON-LD: `Organization`, `Product` (harga RM199 MYR), `FAQPage` (6 soalan, padan
  dengan teks yang dipaparkan)
- `robots.txt` + `sitemap.xml`
- Semua imej ada `width`/`height` (elak CLS) + `alt` bermakna; imej bawah lipatan `lazy`
- Tiada imej hero — LCP ialah teks, jadi tiada muat turun besar sebelum paint
- Mobile-first, tap target ≥ 44px, skip link, `:focus-visible`, `prefers-reduced-motion`

Perlu dibuat di luar kod:

- Hantar `sitemap.xml` dalam Google Search Console selepas domain hidup
- Tuntut & sahkan Google Business Profile. Kalau ada alamat premis fizikal, beritahu —
  boleh tambah schema `LocalBusiness` dengan NAP yang sepadan (sekarang guna
  `Organization` sahaja sebab tiada alamat)
- Kumpul review Google untuk sokong local SEO
- Pantau Search Console & kekalkan kandungan segar

## Cadangan modul baharu

[`docs/modul-kpi/`](docs/modul-kpi/) — cadangan reka bentuk untuk **modul KPI,
prestasi & bonus staf**: spesifikasi fungsi, model data, formula pengiraan, pelan
pembinaan dan senarai keputusan yang perlu dibuat sebelum coding bermula.

Status: cadangan sahaja — belum diluluskan, belum dibina, dan belum menyentuh
landing page ini.
