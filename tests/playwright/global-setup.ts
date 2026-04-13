import { chromium, type FullConfig } from '@playwright/test';

/**
 * Global setup: authenticate as admin once and save the storage state.
 * All test projects reuse this state so they run with an active session.
 */
async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0].use.baseURL ?? 'http://localhost:8080';

  const browser = await chromium.launch();
  const page = await browser.newPage({ baseURL });

  // Navigate to the login page
  await page.goto('/?com=1&option=login');
  await page.waitForLoadState('domcontentloaded');

  // Submit admin credentials
  await page.locator('#geb').fill('poule@vvalem.nl');
  await page.locator('#wac').fill('Jvbdesigns1');
  await page.getByRole('button', { name: 'Inloggen' }).click();
  await page.waitForLoadState('domcontentloaded');

  // Persist the authenticated session for all test workers
  await page.context().storageState({ path: 'auth-state.json' });
  await browser.close();
}

export default globalSetup;
