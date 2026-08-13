// Derives the assets that are generated rather than authored:
//   icons/apple-touch-icon.png  180x180, flattened onto the brand background
//                               (iOS renders transparency as black)
//   img/og.png                  1200x630 social share card, from tools/og.html
//
// Source of truth for the mark is icons/icon-512.png — replace that file and
// re-run this script. Run with: NODE_PATH=$(npm root -g) node tools/render.js
const path = require('path');
const { chromium } = require('playwright');

const root = path.resolve(__dirname, '..');
const BRAND_BG = '#0b1a11';

(async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage();

  const markUrl = 'file://' + path.join(root, 'icons/icon-512.png');
  await page.setViewportSize({ width: 180, height: 180 });
  await page.setContent(
    `<!doctype html><meta charset="utf-8">
     <style>html,body{margin:0;padding:0;background:${BRAND_BG}}
     img{display:block;width:180px;height:180px}</style>
     <img src="${markUrl}">`,
    { waitUntil: 'load' }
  );
  await page.screenshot({ path: path.join(root, 'icons/apple-touch-icon.png') });
  console.log('icons/apple-touch-icon.png  180x180');

  await page.setViewportSize({ width: 1200, height: 630 });
  await page.goto('file://' + path.join(root, 'tools/og.html'), { waitUntil: 'networkidle' });
  await page.screenshot({ path: path.join(root, 'img/og.png') });
  console.log('img/og.png  1200x630');

  await browser.close();
})();
