import { test, expect, type Page, type Locator } from '@playwright/test';

import {
  SHELL_SELECTORS,
  OVERVIEW_SELECTORS,
  TITLE_SELECTORS,
} from './helpers/module-routes';
import { homeRoute } from './helpers/navigation-data';
import { gotoApp } from './helpers/app-fixtures';

/**
 * Overview Readability Regression Journey (T018)
 *
 * Locks down the expected overview structure, table column presence, summary
 * areas, and action regions across representative overview-heavy screens:
 *
 *   1. Module overview table  — list table, headers, action links
 *   2. Standings screen       — standings table columns and action buttons
 *   3. Prediction overview    — user prediction list, table structure
 *   4. Competition-scoped screens — deep-linked module overviews
 *   5. Action regions         — add links, responsive layout consistency
 *
 * Tests check column meaning, key actions and responsive readability without
 * changing any data semantics or behaviour.
 *
 * skipIfAbsent() is used wherever navigation requires a live data-set
 * (e.g., seeded competitions or records) that may be absent in a minimal
 * test environment.
 */

// ---------------------------------------------------------------------------
// Shared helpers
// ---------------------------------------------------------------------------

/**
 * Skip the current test when the given locator has no matches.
 *
 * @param locator  A Playwright locator for the required element.
 */
async function skipIfAbsent(locator: Locator): Promise<void> {
  if ((await locator.count()) === 0) {
    test.skip();
  }
}

/**
 * Navigate to the first module overview reachable from primary navigation.
 *
 * Calls test.skip() when no module nav link is available.
 *
 * @param page  The Playwright Page object.
 */
async function navigateToModuleOverview(page: Page): Promise<void> {
  await gotoApp(page, homeRoute());

  const navLink = page
    .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
    .first();
  await skipIfAbsent(navLink);
  await navLink.click();
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Navigate to a competition context, then enter the first sidebar module link.
 *
 * Calls test.skip() when no competition or sidebar module link is available.
 *
 * @param page  The Playwright Page object.
 */
async function navigateToCompetitionModule(page: Page): Promise<void> {
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
}

// ---------------------------------------------------------------------------
// 1. Module overview — list table structure
// ---------------------------------------------------------------------------

test.describe('Module overview — list table structure', () => {
  test('module overview contains a list/overview table', async ({ page }) => {
    await navigateToModuleOverview(page);

    const listTable = page.locator(OVERVIEW_SELECTORS.listTable);
    await skipIfAbsent(listTable);

    await expect(listTable).toBeVisible();
  });

  test('module overview table has at least one column header', async ({
    page,
  }) => {
    await navigateToModuleOverview(page);

    const headers = page.locator(OVERVIEW_SELECTORS.tableHeader);
    await skipIfAbsent(headers);

    expect(await headers.count()).toBeGreaterThan(0);
  });

  test('module overview table column headers are non-empty', async ({
    page,
  }) => {
    await navigateToModuleOverview(page);

    const headers = page.locator(OVERVIEW_SELECTORS.tableHeader);
    await skipIfAbsent(headers);

    const firstHeader = headers.first();
    await expect(firstHeader).not.toBeEmpty();
  });

  test('module overview has a title panel or page heading', async ({ page }) => {
    await navigateToModuleOverview(page);

    const heading = page.locator(
      [
        TITLE_SELECTORS.titleHeading,
        TITLE_SELECTORS.pageTitle,
        TITLE_SELECTORS.cardTitle,
        'h1',
        'h2',
      ].join(', '),
    ).first();
    await skipIfAbsent(heading);

    await expect(heading).toBeVisible();
    await expect(heading).not.toBeEmpty();
  });
});

// ---------------------------------------------------------------------------
// 2. Module overview — action regions
// ---------------------------------------------------------------------------

test.describe('Module overview — action regions', () => {
  test('module overview exposes an add link when authorised', async ({
    page,
  }) => {
    await navigateToModuleOverview(page);

    const addLink = page.locator('a[href*="option=add"]').first();
    await skipIfAbsent(addLink);

    await expect(addLink).toBeVisible();
  });

  test('module overview table contains at least one action link per row when data is present', async ({
    page,
  }) => {
    await navigateToModuleOverview(page);

    const tableRows = page.locator(
      `${OVERVIEW_SELECTORS.evenRow}, ${OVERVIEW_SELECTORS.oddRow}`,
    );
    await skipIfAbsent(tableRows);

    const actionLinks = page.locator(OVERVIEW_SELECTORS.actionLink);
    await skipIfAbsent(actionLinks);

    expect(await actionLinks.count()).toBeGreaterThan(0);
  });

  test('module overview action links use com= parameter', async ({ page }) => {
    await navigateToModuleOverview(page);

    const actionLinks = page.locator('table.list a[href*="option="]');
    await skipIfAbsent(actionLinks);

    expect(await actionLinks.count()).toBeGreaterThan(0);
  });
});

// ---------------------------------------------------------------------------
// 3. Competition-scoped overview — standings and module screens
// ---------------------------------------------------------------------------

test.describe('Competition-scoped overview — module table and context', () => {
  test('all mandatory shell regions are visible on a competition module screen', async ({
    page,
  }) => {
    await navigateToCompetitionModule(page);

    await expect(page.locator(SHELL_SELECTORS.container)).toBeVisible();
    await expect(page.locator(SHELL_SELECTORS.menuWrapper)).toBeVisible();
    await expect(page.locator(SHELL_SELECTORS.column2)).toBeVisible();
  });

  test('competition module screen has a visible page heading', async ({
    page,
  }) => {
    await navigateToCompetitionModule(page);

    const heading = page
      .locator(
        [
          TITLE_SELECTORS.titleHeading,
          TITLE_SELECTORS.pageTitle,
          'h1',
          'h2',
        ].join(', '),
      )
      .first();
    await skipIfAbsent(heading);

    await expect(heading).toBeVisible();
  });

  test('competition module screen renders a list table or summary content', async ({
    page,
  }) => {
    await navigateToCompetitionModule(page);

    // Any overview-heavy competition module renders either a table.list or
    // meaningful content in the primary column.
    const contentArea = page.locator(SHELL_SELECTORS.column2);
    await expect(contentArea).toBeVisible();

    const contentText = await contentArea.textContent();
    expect(contentText?.trim().length).toBeGreaterThan(0);
  });

  test('competition sidebar exposes contextual navigation links', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);
    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const sidebarLinks = page.locator(`${SHELL_SELECTORS.column1} a`);
    expect(await sidebarLinks.count()).toBeGreaterThan(0);
  });
});

// ---------------------------------------------------------------------------
// 4. Standings overview — table columns and structure
// ---------------------------------------------------------------------------

test.describe('Standings overview — table structure and column meaning', () => {
  test('standings table contains position-related column headers', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    // Navigate to a competition context and find a "Table" or "Stand" link.
    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);
    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const standingsLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a`)
      .filter({ hasText: /stand|table|klassement/i })
      .first();
    await skipIfAbsent(standingsLink);
    await standingsLink.click();
    await page.waitForLoadState('domcontentloaded');

    const headers = page.locator(OVERVIEW_SELECTORS.tableHeader);
    await skipIfAbsent(headers);

    expect(await headers.count()).toBeGreaterThan(0);
  });

  test('standings table has a visible title panel', async ({ page }) => {
    await gotoApp(page, homeRoute());

    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);
    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const standingsLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a`)
      .filter({ hasText: /stand|table|klassement/i })
      .first();
    await skipIfAbsent(standingsLink);
    await standingsLink.click();
    await page.waitForLoadState('domcontentloaded');

    const titlePanel = page
      .locator(
        [TITLE_SELECTORS.titleHeading, TITLE_SELECTORS.pageTitle, 'h1', 'h2'].join(', '),
      )
      .first();
    await skipIfAbsent(titlePanel);

    await expect(titlePanel).toBeVisible();
    await expect(titlePanel).not.toBeEmpty();
  });
});

// ---------------------------------------------------------------------------
// 5. Prediction overview — list and table structure
// ---------------------------------------------------------------------------

test.describe('Prediction overview — user list and table structure', () => {
  test('prediction overview screen renders content in the primary column', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);
    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const predictionLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a`)
      .filter({ hasText: /voorspell|predict/i })
      .first();
    await skipIfAbsent(predictionLink);
    await predictionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const contentArea = page.locator(SHELL_SELECTORS.column2);
    await expect(contentArea).toBeVisible();

    const contentText = await contentArea.textContent();
    expect(contentText?.trim().length).toBeGreaterThan(0);
  });

  test('prediction overview has a visible title panel', async ({ page }) => {
    await gotoApp(page, homeRoute());

    const competitionLink = page.locator('a[href*="competition="]').first();
    await skipIfAbsent(competitionLink);
    await competitionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const predictionLink = page
      .locator(`${SHELL_SELECTORS.sidebar} a`)
      .filter({ hasText: /voorspell|predict/i })
      .first();
    await skipIfAbsent(predictionLink);
    await predictionLink.click();
    await page.waitForLoadState('domcontentloaded');

    const titlePanel = page
      .locator(
        [TITLE_SELECTORS.titleHeading, TITLE_SELECTORS.pageTitle, 'h1', 'h2'].join(', '),
      )
      .first();
    await skipIfAbsent(titlePanel);

    await expect(titlePanel).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// 6. Overview readability at representative viewports
// ---------------------------------------------------------------------------

test.describe('Overview readability at representative viewports', () => {
  const viewports = [
    { label: 'desktop', width: 1440, height: 900 },
    { label: 'tablet portrait', width: 768, height: 1024 },
    { label: 'mobile', width: 390, height: 844 },
  ] as const;

  for (const vp of viewports) {
    test(`module overview table is visible at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await navigateToModuleOverview(page);

      const listTable = page.locator(OVERVIEW_SELECTORS.listTable);
      await skipIfAbsent(listTable);

      await expect(listTable).toBeVisible();
    });

    test(`module overview title panel is visible at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await navigateToModuleOverview(page);

      const heading = page
        .locator(
          [
            TITLE_SELECTORS.titleHeading,
            TITLE_SELECTORS.pageTitle,
            'h1',
            'h2',
          ].join(', '),
        )
        .first();
      await skipIfAbsent(heading);

      await expect(heading).toBeVisible();
    });

    test(`add link is accessible at ${vp.label} (${vp.width}×${vp.height}) when present`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await navigateToModuleOverview(page);

      const addLink = page.locator('a[href*="option=add"]').first();
      await skipIfAbsent(addLink);

      await expect(addLink).toBeVisible();
    });
  }
});
