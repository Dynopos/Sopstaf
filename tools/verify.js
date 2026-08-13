// Functional + SEO verification for index.html, plus reference screenshots.
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
  let types = [];
  try {
    types = JSON.parse(ld)['@graph'].map(n => n['@type']);
    ok(true, 'JSON-LD parses');
  } catch (e) { ok(false, 'JSON-LD parses: ' + e.message); }
  for (const t of ['Organization', 'Product', 'FAQPage']) {
    ok(types.includes(t), `JSON-LD includes ${t}`);
  }

  // FAQ schema must match the visible questions
  const visibleFaq = await page.locator('#faq summary').allInnerTexts();
  const schemaFaq = JSON.parse(ld)['@graph'].find(n => n['@type'] === 'FAQPage')
    .mainEntity.map(q => q.name);
  ok(visibleFaq.length === schemaFaq.length, `FAQ count matches schema (${visibleFaq.length})`);

  // Images must be dimensioned + described
  const imgs = await page.locator('img').evaluateAll(els =>
    els.map(e => ({ src: e.getAttribute('src'), w: e.getAttribute('width'),
                    h: e.getAttribute('height'), alt: e.getAttribute('alt') })));
  ok(imgs.every(i => i.w && i.h), 'every <img> has width+height (CLS)');
  ok(imgs.every(i => i.alt !== null), 'every <img> has an alt attribute');

  // No broken asset references
  const bad = [];
  page.on('response', r => { if (r.status() >= 400) bad.push(r.url()); });
  const assets = await page.evaluate(() =>
    [...document.querySelectorAll('img[src],link[href]')]
      .map(e => e.src || e.href).filter(u => u.startsWith('file:')));
  const fs = require('fs');
  for (const a of assets) {
    if (!fs.existsSync(decodeURI(a.replace('file://', '')))) bad.push(a);
  }
  ok(bad.length === 0, 'all referenced local assets exist' + (bad.length ? ': ' + bad.join(', ') : ''));

  // --- Demo behaviour ---
  const prog = () => page.locator('#dProg').innerText();
  ok((await prog()) === '3/6 siap', `default progress is 3/6 (got ${await prog()})`);

  await page.locator('#dStaf').selectOption({ index: 1 });          // Hafiz
  ok((await prog()) === '1/4 siap', `staff switch updates list (got ${await prog()})`);
  await page.locator('#dStaf').selectOption({ index: 0 });          // back to Aisyah

  await page.locator('#dList .dTask').nth(3).locator('.dTick').click();
  ok(await page.locator('#dModal').evaluate(e => e.classList.contains('on')), 'tick opens confirm modal');
  await page.locator('.dYa').click();
  ok((await prog()) === '4/6 siap', `confirming a tick updates progress (got ${await prog()})`);

  await page.locator('#tabAdmin').click();
  ok(await page.locator('#panAdmin').isVisible(), 'Admin tab reveals boss view');
  const rows = await page.locator('#dAdminList .dAdmin').count();
  ok(rows === 3, `admin view lists all ${rows} staff`);
  await page.locator('#tabStaf').click();

  await page.locator('.dReset').click();
  ok((await prog()) === '3/6 siap', 'reset restores the demo');

  ok(errors.length === 0, 'no JS errors' + (errors.length ? ': ' + errors.join('; ') : ''));

  // --- Screenshots (fresh page so it starts at the top, demo in default state) ---
  const snap = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  await snap.goto(url, { waitUntil: 'networkidle' });
  await snap.screenshot({ path: path.join(root, '.build/shot-hero.png') });
  await snap.locator('#demo .phoneFrame').screenshot({ path: path.join(root, '.build/shot-demo.png') });

  const m = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true });
  await m.goto(url, { waitUntil: 'networkidle' });
  await m.screenshot({ path: path.join(root, '.build/shot-mobile.png') });
  const overflow = await m.evaluate(() =>
    document.documentElement.scrollWidth - document.documentElement.clientWidth);
  ok(overflow <= 0, `no horizontal overflow on 390px (${overflow}px)`);

  await browser.close();
  console.log(out.join('\n'));
  console.log('\n' + out.filter(l => l.startsWith('FAIL')).length + ' failing');
})();
