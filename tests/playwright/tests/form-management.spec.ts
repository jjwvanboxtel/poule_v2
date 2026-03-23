import { test, expect, type Page, type Locator } from '@playwright/test';

import {
  FORM_SELECTORS,
  SHELL_SELECTORS,
  OVERVIEW_SELECTORS,
} from './helpers/module-routes';
import { homeRoute } from './helpers/navigation-data';
import { gotoApp } from './helpers/app-fixtures';

/**
 * Form and Management-Screen Regression Journey (T012)
 *
 * Locks down the expected form structure, field presentation, action rows,
 * and validation/status visibility across three representative screen types:
 *
 *   1. Login form          — authentication screen; email + password + submit
 *   2. Add / create form   — admin module add screen; fields, action row
 *   3. Module overview     — management list with overview table and add link
 *
 * Tests check form actions, methods, field names, submit contracts and
 * action-row clarity without altering any form behaviour.
 *
 * skipIfAbsent() is used wherever navigation requires a live data-set
 * (e.g., module records) that may be absent in a minimal test environment.
 */

// ---------------------------------------------------------------------------
// Shared helpers
// ---------------------------------------------------------------------------

/**
 * Skip the current test when the given locator has no matches.
 *
 * Mirrors the same helper in navigation-shell.spec.ts so that tests in
 * this file also abort gracefully when optional content is not present.
 *
 * @param locator  A Playwright locator for the required element.
 */
async function skipIfAbsent(locator: Locator): Promise<void> {
  if ((await locator.count()) === 0) {
    test.skip();
  }
}

/**
 * Navigate to the application login screen by following the first login
 * link found anywhere on the current page.
 *
 * Calls test.skip() when no login link is available so that the entire
 * test is marked as skipped rather than failed.
 *
 * @param page  The Playwright Page object.
 */
async function navigateToLoginForm(page: Page): Promise<void> {
  await gotoApp(page, homeRoute());
  const loginLink = page.locator('a[href*="option=login"]').first();
  await skipIfAbsent(loginLink);
  await loginLink.click();
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Navigate to the first add/create form found after entering a module
 * overview screen via the primary navigation.
 *
 * Calls test.skip() when no module nav link or no add link is available.
 *
 * @param page  The Playwright Page object.
 */
async function navigateToAddForm(page: Page): Promise<void> {
  await gotoApp(page, homeRoute());

  // Enter the first module overview reachable from primary navigation.
  const navLink = page
    .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
    .first();
  await skipIfAbsent(navLink);
  await navLink.click();
  await page.waitForLoadState('domcontentloaded');

  // Locate and follow the add link within the current screen.
  const addLink = page.locator('a[href*="option=add"]').first();
  await skipIfAbsent(addLink);
  await addLink.click();
  await page.waitForLoadState('domcontentloaded');
}

// ---------------------------------------------------------------------------
// 1. Login form — structure and field contract
// ---------------------------------------------------------------------------

test.describe('Login form — structure and field contract', () => {
  test('login form is present on the login screen', async ({ page }) => {
    await navigateToLoginForm(page);

    await expect(page.locator(FORM_SELECTORS.form)).toBeVisible();
  });

  test('login form uses the POST method', async ({ page }) => {
    await navigateToLoginForm(page);

    const form = page.locator(FORM_SELECTORS.form).first();
    await skipIfAbsent(form);

    const method = await form.getAttribute('method');
    expect(method?.toLowerCase()).toBe('post');
  });

  test('login form has a non-empty action attribute', async ({ page }) => {
    await navigateToLoginForm(page);

    const form = page.locator(FORM_SELECTORS.form).first();
    await skipIfAbsent(form);

    const action = await form.getAttribute('action');
    expect(action).toBeTruthy();
  });

  test('login form contains a visible text input for the username/email', async ({
    page,
  }) => {
    await navigateToLoginForm(page);

    await expect(page.locator(FORM_SELECTORS.textInput)).toBeVisible();
  });

  test('login form contains a visible password input', async ({ page }) => {
    await navigateToLoginForm(page);

    await expect(page.locator(FORM_SELECTORS.passwordInput)).toBeVisible();
  });

  test('login form has a submit button with name="submit"', async ({
    page,
  }) => {
    await navigateToLoginForm(page);

    await expect(page.locator(FORM_SELECTORS.submitButton)).toBeVisible();
  });

  test('login form fields are wrapped in a table.list layout element', async ({
    page,
  }) => {
    await navigateToLoginForm(page);

    await expect(page.locator(FORM_SELECTORS.formTable)).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// 2. Login form — feedback and recovery area
// ---------------------------------------------------------------------------

test.describe('Login form — feedback and recovery links', () => {
  test('login screen exposes a recovery or new-account link area', async ({
    page,
  }) => {
    await navigateToLoginForm(page);

    // The login screen renders a "#login_lost" div with new-account /
    // lost-password links beneath the form.
    const recoveryArea = page.locator('#login_lost');
    await skipIfAbsent(recoveryArea);

    await expect(recoveryArea).toBeVisible();
  });

  test('the recovery area contains at least one link', async ({ page }) => {
    await navigateToLoginForm(page);

    const recoveryArea = page.locator('#login_lost');
    await skipIfAbsent(recoveryArea);

    const links = recoveryArea.locator('a');
    expect(await links.count()).toBeGreaterThan(0);
  });
});

// ---------------------------------------------------------------------------
// 3. Admin add form — structure, action row, and submit contract
// ---------------------------------------------------------------------------

test.describe('Admin add form — structure, action row, and contract', () => {
  test('an add form is present after following an add link', async ({
    page,
  }) => {
    await navigateToAddForm(page);

    await expect(page.locator(FORM_SELECTORS.form)).toBeVisible();
  });

  test('admin add form uses the POST method', async ({ page }) => {
    await navigateToAddForm(page);

    const form = page.locator(FORM_SELECTORS.form).first();
    await skipIfAbsent(form);

    const method = await form.getAttribute('method');
    expect(method?.toLowerCase()).toBe('post');
  });

  test('admin add form action posts to the current page (empty or self-URL)', async ({
    page,
  }) => {
    await navigateToAddForm(page);

    const form = page.locator(FORM_SELECTORS.form).first();
    await skipIfAbsent(form);

    // The existing templates use action="" to post back to the current URL.
    // An empty string or the same path both satisfy the self-post contract.
    const action = await form.getAttribute('action');
    expect(action === '' || action === null || action === page.url()).toBeTruthy();
  });

  test('admin add form has a submit button with name="submit"', async ({
    page,
  }) => {
    await navigateToAddForm(page);

    await expect(page.locator(FORM_SELECTORS.submitButton)).toBeVisible();
  });

  test('admin add form has a cancel / back button', async ({ page }) => {
    await navigateToAddForm(page);

    await expect(page.locator(FORM_SELECTORS.cancelButton)).toBeVisible();
  });

  test('admin add form fields are wrapped in a table.list layout element', async ({
    page,
  }) => {
    await navigateToAddForm(page);

    await expect(page.locator(FORM_SELECTORS.formTable)).toBeVisible();
  });

  test('admin add form contains at least one visible text input field', async ({
    page,
  }) => {
    await navigateToAddForm(page);

    const inputs = page.locator(FORM_SELECTORS.textInput);
    await skipIfAbsent(inputs);

    await expect(inputs.first()).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// 4. Module overview — management table and add link
// ---------------------------------------------------------------------------

test.describe('Module overview — management table and add link', () => {
  test('module overview contains a list/overview table', async ({ page }) => {
    await gotoApp(page, homeRoute());

    const navLink = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(navLink);
    await navLink.click();
    await page.waitForLoadState('domcontentloaded');

    const listTable = page.locator(OVERVIEW_SELECTORS.listTable);
    await skipIfAbsent(listTable);

    await expect(listTable).toBeVisible();
  });

  test('module overview exposes an add link for authorised users', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const navLink = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(navLink);
    await navLink.click();
    await page.waitForLoadState('domcontentloaded');

    const addLink = page.locator('a[href*="option=add"]').first();
    await skipIfAbsent(addLink);

    await expect(addLink).toBeVisible();
  });

  test('module overview table has at least one column header', async ({
    page,
  }) => {
    await gotoApp(page, homeRoute());

    const navLink = page
      .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
      .first();
    await skipIfAbsent(navLink);
    await navLink.click();
    await page.waitForLoadState('domcontentloaded');

    const headers = page.locator(OVERVIEW_SELECTORS.tableHeader);
    await skipIfAbsent(headers);

    expect(await headers.count()).toBeGreaterThan(0);
  });
});

// ---------------------------------------------------------------------------
// 5. Form rendering at representative viewports
// ---------------------------------------------------------------------------

test.describe('Form rendering at representative viewports', () => {
  const viewports = [
    { label: 'desktop', width: 1440, height: 900 },
    { label: 'tablet portrait', width: 768, height: 1024 },
    { label: 'mobile', width: 390, height: 844 },
  ] as const;

  for (const vp of viewports) {
    test(`login form submit button is visible at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await gotoApp(page, homeRoute());

      const loginLink = page.locator('a[href*="option=login"]').first();
      await skipIfAbsent(loginLink);
      await loginLink.click();
      await page.waitForLoadState('domcontentloaded');

      await expect(page.locator(FORM_SELECTORS.submitButton)).toBeVisible();
    });

    test(`admin add form submit button is accessible at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await gotoApp(page, homeRoute());

      const navLink = page
        .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
        .first();
      await skipIfAbsent(navLink);
      await navLink.click();
      await page.waitForLoadState('domcontentloaded');

      const addLink = page.locator('a[href*="option=add"]').first();
      await skipIfAbsent(addLink);
      await addLink.click();
      await page.waitForLoadState('domcontentloaded');

      await expect(page.locator(FORM_SELECTORS.submitButton)).toBeVisible();
    });

    test(`module overview list table is visible at ${vp.label} (${vp.width}×${vp.height})`, async ({
      page,
    }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await gotoApp(page, homeRoute());

      const navLink = page
        .locator(`${SHELL_SELECTORS.menu} a[href*="com="]`)
        .first();
      await skipIfAbsent(navLink);
      await navLink.click();
      await page.waitForLoadState('domcontentloaded');

      const listTable = page.locator(OVERVIEW_SELECTORS.listTable);
      await skipIfAbsent(listTable);

      await expect(listTable).toBeVisible();
    });
  }
});
