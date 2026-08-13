// Functional + SEO verification for index.html, plus reference screenshots.
// Run: NODE_PATH=$(npm root -g) node tools/verify.js
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const root = path.resolve(__dirname, '..');
const url = 'file://' + path.join(root, 'index.html');
const out = [];
const ok = (c, m) => out.push(`${c ? 'PASS' : 'FAIL'}  ${m}`);

(async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  const errors = [];
  page.on('pageerror', e => errors.push(e.message));
  await page.goto(url, { waitUntil: 'networkidle' });

  // --- SEO structure ---
  ok((await page.locator('h1').count()) === 1, 'exactly one <h1>');
  const h1 = (await page.locator('h1').innerText()).toLowerCase();
  ok(h1.includes('sop') && h1.includes('staf'), 'h1 carries primary keyword ("sop", "staf")');

  const title = await page.title();
  ok(title.length > 0 && title.length <= 60, `title length ${title.length} <= 60`);

  const desc = await page.getAttribute('meta[name="description"]', 'content');
  ok(desc.length >= 120 && desc.length <= 165, `meta description length ${desc.length} in 120-165`);

  ok(!!(await page.locator('link[rel="canonical"]').count()), 'canonical present');
  for (const p of ['og:title', 'og:description', 'og:image', 'og:url', 'og:type']) {
    ok(!!(await page.locator(`meta[property="${p}"]`).count()), `${p} present`);
  }
  ok(!!(await page.locator('meta[name="twitter:card"]').count()), 'twitter:card present');
  ok((await page.getAttribute('html', 'lang')) === 'ms', 'html lang="ms"');

  const ld = await page.locator('script[type="application/ld+json"]').innerText();
  let graph = [];
  try {
    graph = JSON.parse(ld)['@graph'];
    ok(true, 'JSON-LD parses');
  } catch (e) { ok(false, 'JSON-LD parses: ' + e.message); }
  const types = graph.map(n => n['@type']);
  for (const t of ['Organization', 'Product', 'FAQPage']) {
    ok(types.includes(t), `JSON-LD includes ${t}`);
  }

  // FAQ schema must match the visible questions
  const visibleFaq = await page.locator('#faq summary').allInnerTexts();
  const schemaFaq = graph.find(n => n['@type'] === 'FAQPage').mainEntity.map(q => q.name);
  ok(visibleFaq.length === schemaFaq.length, `FAQ count matches schema (${visibleFaq.length})`);
  ok(visibleFaq.every((q, i) => q.trim() === schemaFaq[i].trim()),
     'FAQ question text matches schema word for word');

  // Pricing must state the annual fee as mandatory, not optional
  const harga = await page.locator('#harga').innerText();
  ok(/RM199/.test(harga) && /RM99/.test(harga), 'both prices shown');
  ok(/[Ww]ajib/.test(harga), 'annual fee marked as wajib (mandatory)');
  ok(!/pilihan\b(?!\.)/.test(harga.replace('bukan pilihan', '')),
     'annual fee not described as optional');

  // The page must not promise a live demo that does not exist
  const body = await page.locator('body').innerText();
  ok(!/demo percuma/i.test(body), 'no "demo percuma" promise');
  ok(!/\b(1 hari|satu hari|sehari)\b/i.test(body), 'no one-day installation promise');

  // Images must be dimensioned + described
  const imgs = await page.locator('img').evaluateAll(els =>
    els.map(e => ({ w: e.getAttribute('width'), h: e.getAttribute('height'),
                    alt: e.getAttribute('alt') })));
  ok(imgs.every(i => i.w && i.h), 'every <img> has width+height (CLS)');
  ok(imgs.every(i => i.alt !== null), 'every <img> has an alt attribute');

  // No broken asset references
  const assets = await page.evaluate(() =>
    [...document.querySelectorAll('img[src],link[href]')]
      .map(e => e.src || e.href).filter(u => u.startsWith('file:')));
  const bad = assets.filter(a => !fs.existsSync(decodeURI(a.replace('file://', ''))));
  ok(bad.length === 0, 'all referenced local assets exist' + (bad.length ? ': ' + bad.join(', ') : ''));

  // --- Demo behaviour: BOTH phones must be live and stay in sync ---
  const phones = page.locator('.appPhone');
  const nPhones = await phones.count();
  ok(nPhones === 2, `two interactive phones on the page (${nPhones})`);

  const hero = phones.nth(0), demo = phones.nth(1);
  const prog = l => l.locator('.js-prog').innerText();

  // every tick must be a real button, in both phones
  const tickTags = await page.locator('.dTick').evaluateAll(els => [...new Set(els.map(e => e.tagName))]);
  ok(tickTags.length === 1 && tickTags[0] === 'BUTTON', `all ticks are <button> (${tickTags})`);

  ok((await prog(hero)) === '3/6 siap', `hero starts 3/6 (${await prog(hero)})`);
  ok((await prog(demo)) === '3/6 siap', `demo starts 3/6 (${await prog(demo)})`);

  // tick in the HERO phone — this is what was dead before
  await hero.locator('.js-tick').nth(3).click();
  ok(await hero.locator('.js-modal').evaluate(e => e.classList.contains('on')),
     'tick in hero opens the confirm modal');
  await hero.locator('.js-jawab[data-ya="1"]').click();
  ok((await prog(hero)) === '4/6 siap', `hero tick updates progress (${await prog(hero)})`);
  ok((await prog(demo)) === '4/6 siap', 'both phones stay in sync');

  // "Tidak" must close without changing anything
  await demo.locator('.js-tick').nth(4).click();
  await demo.locator('.js-jawab[data-ya="0"]').click();
  ok((await prog(demo)) === '4/6 siap', 'answering Tidak changes nothing');

  // tick in the DEMO phone
  await demo.locator('.js-tick').nth(4).click();
  await demo.locator('.js-jawab[data-ya="1"]').click();
  ok((await prog(hero)) === '5/6 siap', `demo tick updates both (${await prog(hero)})`);

  await demo.locator('.js-staf').selectOption({ index: 1 });
  ok((await prog(demo)) === '1/4 siap', `staff switch updates list (${await prog(demo)})`);
  ok((await prog(hero)) === '1/4 siap', 'staff switch mirrors to the other phone');

  await demo.locator('.js-tab[data-tab="admin"]').click();
  ok(await demo.locator('.js-pan-admin').isVisible(), 'Admin tab reveals boss view');
  ok((await demo.locator('.js-admin .dAdmin').count()) === 3, 'admin view lists all 3 staff');
  await demo.locator('.js-tab[data-tab="staf"]').click();

  await demo.locator('.js-reset').click();
  ok((await prog(demo)) === '3/6 siap', 'reset restores the demo');

  ok(errors.length === 0, 'no JS errors' + (errors.length ? ': ' + errors.join('; ') : ''));

  // --- Mobile: the hero phone is what a prospect taps first ---
  const m = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await m.goto(url, { waitUntil: 'networkidle' });
  const mHero = m.locator('.appPhone').first();
  await mHero.locator('.js-tick').nth(3).tap();
  await mHero.locator('.js-jawab[data-ya="1"]').tap();
  ok((await mHero.locator('.js-prog').innerText()) === '4/6 siap',
     'tapping the hero phone works on mobile (touch)');
  const overflow = await m.evaluate(() =>
    document.documentElement.scrollWidth - document.documentElement.clientWidth);
  ok(overflow <= 0, `no horizontal overflow on 390px (${overflow}px)`);

  // --- Screenshots ---
  const snap = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  await snap.goto(url, { waitUntil: 'networkidle' });
  await snap.screenshot({ path: path.join(root, '.build/shot-hero.png') });
  await snap.locator('#demo .phoneFrame').screenshot({ path: path.join(root, '.build/shot-demo.png') });
  await m.screenshot({ path: path.join(root, '.build/shot-mobile.png') });

  await browser.close();
  console.log(out.join('\n'));
  console.log('\n' + out.filter(l => l.startsWith('FAIL')).length + ' failing');
})();
