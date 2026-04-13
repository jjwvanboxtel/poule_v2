import { test as base, expect, type Page } from '@playwright/test';

/**
 * Extended Playwright fixtures shared across all poule_v2 UI regression
 * journey specs.
 *
 * Usage:
 *   import { test, expect } from '../helpers/app-fixtures';
 */

/** Fixture types exposed to test specs. */
export type AppFixtures = {
  /** A Page that has already navigated to the application base URL. */
  appPage: Page;
};

/**
 * Extended test object with the `appPage` fixture pre-configured.
 *
 * Every test that uses `appPage` gets a fresh browser context with the
 * application's base URL already loaded, so each spec can skip the
 * boilerplate navigation step.
 */
export const test = base.extend<AppFixtures>({
  appPage: async ({ page }, use) => {
    await page.goto('/');
    await use(page);
  },
});

export { expect };

/**
 * Navigate to a relative path within the application and wait for the
 * response to be committed to the DOM.
 *
 * @param page    The Playwright Page object.
 * @param path    Relative URL path (e.g. '/?module=competitions').
 */
export async function gotoApp(page: Page, path: string): Promise<void> {
  await page.goto(path);
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Assert that a page-level heading or title panel is visible and contains
 * the expected text.
 *
 * @param page     The Playwright Page object.
 * @param heading  The expected text or substring.
 */
export async function assertPageHeading(
  page: Page,
  heading: string,
): Promise<void> {
  await expect(
    page.locator('h1, h2, .page-title, .card-title').first(),
  ).toContainText(heading);
}

/**
 * Assert that a named navigation link is visible in the primary or
 * contextual navigation area.
 *
 * @param page  The Playwright Page object.
 * @param label The visible link label to check.
 */
export async function assertNavLink(page: Page, label: string): Promise<void> {
  await expect(page.getByRole('link', { name: label })).toBeVisible();
}
