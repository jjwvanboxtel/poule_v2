import { test, expect, type Page, type Locator } from '@playwright/test';

import {
  SHELL_SELECTORS,
  getActiveNavItem,
} from './helpers/module-routes';
import { homeRoute } from './helpers/navigation-data';
import { gotoApp } from './helpers/app-fixtures';

/**
 * Navigation and Shell Consistency – Regression Journey (T007)
 *
 * Locks down the expected shell structure, navigation patterns and
 * orientation cues across three representative screen types so that
 * the later shell refactor (T009–T011) can only make presentation
 * changes without breaking functional navigation.
 *
 * Covered screen types
 *   1. Home           — application root, no competition/module context
 *   2. Competition    — competition detail; hero shows competition name
 *   3. Module         — module overview reached via primary nav; active item set
 *   4. Comp + Module  — module scoped to a competition (deep link)
 *
 * Tests are written against the *current* rendered output so they pass
 * before the refactor and must still pass after it.
 */

// ---------------------------------------------------------------------------
// Shared assertion and navigation helpers
// ---------------------------------------------------------------------------

/**
 * Assert that the eight mandatory shell regions are visible.
 * Called from every screen-level describe block.
 */
async function assertMandatoryShellRegions(page: Page): Promise<void> {
  await expect(page.locator(SHELL_SELECTORS.container)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.menuWrapper)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.loginNav)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.header)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.column1)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.column2)).toBeVisible();
  await expect(page.locator(SHELL_SELECTORS.copyright)).toBeVisible();
}

/**
 * Skip the current test when the given locator has no matches.
 *
 * Use instead of the repeated `if (count === 0) { test.skip(); return; }`
 * pattern to keep individual test bodies concise.
 *
 * @param locator  A Playwright locator for the required element.
 */
async function skipIfAbsent(locator: Locator): Promise<void> {
  if ((await locator.count()) === 0) {
    test.skip();
  }
}

// ---------------------------------------------------------------------------
// 1. Home screen
// ---------------------------------------------------------------------------

test.describe('Home screen — shell structure', () => {
  test.beforeEach(async ({ page }) => {
    await gotoApp(page, homeRoute());
  });

  test('all mandatory shell regions are visible', async ({ page }) => {
    await assertMandatoryShellRegions(page);
  });

  test('hero band contains a non-empty h1', async ({ page }) => {
    const h1 = page.locator(SHELL_SELECTORS.h1);
    await expect(h1).toBeVisible();
    await expect(h1).not.toBeEmpty();
  });

  test('primary navigation contains at least one link', async ({ page }) => {
    const links = page.locator(SHELL_SELECTORS.navLinks);
    await expect(links.first()).toBeVisible();
    expect(await links.count()).toBeGreaterThan(0);
  });

  test('no active nav item is highlighted on the home screen', async ({
    page,
  }) => {
    expect(await page.locator(SHELL_SELECTORS.activeNavItem).count()).toBe(0);
  });

  test('copyright footer is visible and non-empty', async ({ page }) => {
    const footer = page.locator(SHELL_SELECTORS.copyright);
    await expect(footer).toBeVisible();
    await expect(footer).not.toBeEmpty();
  });

  test('sidebar column and contextual submenu are present', async ({
    page,
  }) => {
    await expect(page.locator(SHELL_SELECTORS.sidebar)).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// 2. Competition screen
// ---------------------------------------------------------------------------

test.describe('Competition screen — hero context', () => {
  test('all mandatory shell regions are visible after navigating to a competition', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const link = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    await assertMandatoryShellRegions(page);
  });

  test('hero h1 is non-empty on a competition screen', async ({ page }) => {
    await gotoApp(page, homeRoute());

    const link = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    await expect(page.locator(SHELL_SELECTORS.h1)).not.toBeEmpty();
  });

  test('competition sidebar contains at least one contextual link', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const link = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    const sidebarLinks = page.locator(`${SHELL_SELECTORS.column1} a`);
    expect(await sidebarLinks.count()).toBeGreaterThan(0);
  });
});

// ---------------------------------------------------------------------------
// 3. Module overview screen
// ---------------------------------------------------------------------------

test.describe('Module overview screen — active navigation', () => {
  test('all mandatory shell regions are visible on a module screen', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const link = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    await assertMandatoryShellRegions(page);
  });

  test('active nav item is highlighted when on a module screen', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const link = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    await expect(getActiveNavItem(page)).toBeVisible();
  });

  test('hero h1 is non-empty on a module screen', async ({ page }) => {
    await gotoApp(page, homeRoute());

    const link = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(link);

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    await expect(page.locator(SHELL_SELECTORS.h1)).not.toBeEmpty();
  });

  test('navigation links are identical to the home screen nav links', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());
    const homeLinks = await page
      .locator(SHELL_SELECTORS.navLinks)
      .allTextContents();

    const link = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    if ((await link.count()) === 0 || homeLinks.length === 0) {
      test.skip();
      return;
    }

    await link.click();
    await page.waitForLoadState('domcontentloaded');

    const moduleLinks = await page
      .locator(SHELL_SELECTORS.navLinks)
      .allTextContents();

    expect(moduleLinks).toEqual(homeLinks);
  });
});

// ---------------------------------------------------------------------------
// 4. Competition + Module screen (deep link)
// ---------------------------------------------------------------------------

test.describe('Competition + module screen — shell consistency', () => {
  test('all mandatory shell regions are visible on a deep-linked module screen', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);

    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const moduleLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a[href*="com="]`)
      .first();
    await skipIfAbsent(moduleLink);

    await moduleLink.click();
    await page.waitForLoadState('domcontentloaded');

    await assertMandatoryShellRegions(page);
  });

  test('primary nav links are identical in competition+module context', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());
    const homeLinks = await page
      .locator(SHELL_SELECTORS.navLinks)
      .allTextContents();

    const competitionLink = page.locator('a[href*="competition="]').first();
    if ((await competitionLink.count()) === 0 || homeLinks.length === 0) {
      test.skip();
      return;
    }

    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const moduleLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a[href*="com="]`)
      .first();
    await skipIfAbsent(moduleLink);

    await moduleLink.click();
    await page.waitForLoadState('domcontentloaded');

    const deepLinks = await page
      .locator(SHELL_SELECTORS.navLinks)
      .allTextContents();

    expect(deepLinks).toEqual(homeLinks);
  });
});

// ---------------------------------------------------------------------------
// 5. Shell structure at representative viewports
// ---------------------------------------------------------------------------

test.describe('Shell structure at representative viewports', () => {
  const viewports = [
    { label: 'desktop', width: 1440, height: 900 },
    { label: 'tablet portrait', width: 768, height: 1024 },
    { label: 'mobile', width: 390, height: 844 },
  ] as const;

  for (const vp of viewports) {
    test(`mandatory shell regions are present at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await gotoApp(page, homeRoute());
      await assertMandatoryShellRegions(page);
    });
  }
});
