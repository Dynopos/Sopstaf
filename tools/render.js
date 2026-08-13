// Asset renderer: rasterises icons/logo.svg into the PNG icon set and, when
// .build/og.html is present, the 1200x630 social share card.
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const root = path.resolve(__dirname, '..');
const svg = fs.readFileSync(path.join(root, 'icons/logo.svg'), 'utf8');

const ICONS = [
  { size: 192, out: 'icons/icon-192.png' },
  { size: 512, out: 'icons/icon-512.png' },
  { size: 180, out: 'icons/apple-touch-icon.png' },
];

(async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage();

  for (const { size, out } of ICONS) {
    await page.setViewportSize({ width: size, height: size });
    await page.setContent(
      `<!doctype html><meta charset="utf-8">
       <style>html,body{margin:0;padding:0;background:transparent}
       svg{display:block;width:${size}px;height:${size}px}</style>${svg}`,
      { waitUntil: 'load' }
    );
    await page.screenshot({ path: path.join(root, out), omitBackground: true });
    console.log(`${out}  ${size}x${size}`);
  }

  const og = path.join(root, 'tools/og.html');
  if (fs.existsSync(og)) {
    await page.setViewportSize({ width: 1200, height: 630 });
    await page.goto('file://' + og, { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(root, 'img/og.png') });
    console.log('img/og.png  1200x630');
  }

  await browser.close();
})();
