import { chromium } from 'playwright';

const BASE = process.env.SMOKE_BASE_URL || 'http://204.168.131.195';

function assert(cond, msg) {
  if (!cond) throw new Error(msg);
}

function pickText(arr) {
  return arr.filter(Boolean).join('\n');
}

const consoleErrors = [];
const failedResponses = [];
const missingAssets = [];

const browser = await chromium.launch();
const page = await browser.newPage();

page.on('console', (msg) => {
  if (msg.type() === 'error') consoleErrors.push(msg.text());
});

page.on('response', (res) => {
  if (res.status() >= 500) failedResponses.push(`${res.status()} ${res.url()}`);
  if (res.status() === 404) {
    const url = res.url();
    if (!url.endsWith('/favicon.ico') && !url.endsWith('/robots.txt')) {
      missingAssets.push(url);
    }
  }
});

try {
  // 1) Home
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[type="search"]', { timeout: 15000 });

  // 2) Type query and wait for suggestions
  const input = page.locator('input[type="search"]');
  await input.fill('62');

  const suggestion = page.locator('ul li a').first();
  await suggestion.waitFor({ timeout: 15000 });

  const href = await suggestion.getAttribute('href');
  assert(href && href.includes('/code/'), `Expected suggestion href with /code/, got: ${href}`);

  // 3) Click suggestion -> code detail
  await Promise.all([
    page.waitForURL(/\/code\/[^/]+\/[^/]+/),
    suggestion.click(),
  ]);

  // 4) Check code detail has mapping panel header
  await page.waitForSelector('main h1', { timeout: 15000 });
  const h1 = await page.locator('main h1').first().textContent();
  assert(h1 && h1.includes('62'), `Expected main h1 to include code, got: ${h1}`);

  await page.waitForSelector('text=Відповідність КВЕД → NACE 2.1-UA', { timeout: 15000 });

  // 5) Catalog
  await page.goto(`${BASE}/catalog`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('text=Дерево класифікатора', { timeout: 15000 });

  // ensure at least one node exists
  await page.locator('ul > li').first().waitFor({ timeout: 15000 });

  // report
  if (failedResponses.length) {
    throw new Error(
      pickText([
        failedResponses.length ? `500+ responses:\n${failedResponses.join('\n')}` : '',
        consoleErrors.length ? `Console errors:\n${consoleErrors.join('\n')}` : '',
        missingAssets.length ? `404 responses:\n${missingAssets.join('\n')}` : '',
      ])
    );
  }

  // Treat missing assets as warning (common for favicon etc.).
  if (consoleErrors.length || missingAssets.length) {
    console.warn(pickText([
      consoleErrors.length ? `Console errors:\n${consoleErrors.join('\n')}` : '',
      missingAssets.length ? `404 responses:\n${missingAssets.join('\n')}` : '',
    ]));
  }

  console.log('SMOKE_OK');
} finally {
  await page.close();
  await browser.close();
}

