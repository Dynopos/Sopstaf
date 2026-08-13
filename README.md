# DYNO SOP Staf — Landing Page

Landing page statik (satu fail HTML, tanpa build step) untuk **DYNO SOP Staf** —
sistem SOP & reminder tugasan automatik untuk staf bisnes kecil di Malaysia.

Bahasa: Bahasa Melayu (`lang="ms"`). Tiada dependency runtime, tiada font/CDN luar —
semua CSS & JS inline supaya first paint pantas dan Core Web Vitals kekal elok.

## Struktur

```
index.html          Landing page penuh (CSS + JS inline)
robots.txt          Benarkan semua crawler + rujukan sitemap
sitemap.xml         Satu URL
site.webmanifest    Metadata PWA + ikon
icons/logo.svg      Logo vektor (sumber untuk semua ikon PNG)
icons/*.png         Ikon 192 / 512 / apple-touch, dijana dari logo.svg
img/og.png          Kad kongsi sosial 1200x630 (WhatsApp / FB / X)
tools/render.js     Jana semula ikon + kad OG (Playwright + Chromium)
tools/og.html       Sumber reka bentuk kad OG
tools/verify.js     Semakan SEO + ujian fungsi demo + screenshot
```

## Preview lokal

```bash
npx http-server . -p 8080     # buka http://localhost:8080
```

## Sebelum launch

**Domain** sudah ditetapkan kepada `https://sopstaf.dynopro.my` — canonical, Open Graph,
Twitter Card, JSON-LD, `robots.txt` dan `sitemap.xml` semua menunjuk ke situ. Kalau
bertukar kemudian, ganti sekali gus:

```bash
grep -rl 'sopstaf\.dynopro\.my' index.html robots.txt sitemap.xml \
  | xargs sed -i 's|https://sopstaf\.dynopro\.my|https://DOMAIN-BARU|g'
```

**Logo masih perlu diganti.** `icons/logo.svg` ialah **lukisan ganti** yang saya buat —
ia *bukan* logo DYNO sebenar (fail asal tak sampai ke repo, hanya gambar dalam chat).
Ganti dengan yang sebenar, kemudian jana semula ikon + kad OG:

```bash
# Pilihan A — ada fail SVG: tulis ganti icons/logo.svg, lepas tu:
NODE_PATH=$(npm root -g) node tools/render.js

# Pilihan B — ada fail PNG sahaja: letak terus sebagai
#   icons/icon-192.png, icons/icon-512.png, icons/apple-touch-icon.png
#   dan JANGAN jalankan tools/render.js (ia akan tulis ganti fail tersebut).
#   Untuk kad OG, jalankan render.js selepas kemas kini tools/og.html.
```

## Semakan sebelum push

```bash
NODE_PATH=$(npm root -g) node tools/verify.js
```

Menyemak 29 perkara: struktur SEO (satu `<h1>`, panjang title/description, canonical,
Open Graph, Twitter Card, JSON-LD, padanan soalan FAQ dengan schema), setiap `<img>`
ada `width`/`height`/`alt`, tiada rujukan aset yang rosak, tiada horizontal scroll pada
390px, dan demo interaktif betul-betul berfungsi (tukar staf, tick, tab Admin, reset).
Screenshot disimpan dalam `.build/` (tidak di-commit).

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

Perlu dibuat di luar kod (rujuk checklist SEO 39/41/44):

- Hantar `sitemap.xml` dalam Google Search Console selepas domain hidup
- Tuntut & sahkan Google Business Profile. Kalau ada alamat premis fizikal, beritahu —
  saya boleh tambah schema `LocalBusiness` dengan NAP yang sepadan (sekarang guna
  `Organization` sahaja sebab tiada alamat)
- Kumpul review Google untuk sokong local SEO
- Pantau Search Console & kekalkan kandungan segar
