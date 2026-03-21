import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for poule_v2 UI regression tests.
 *
 * Base URL is read from the POULE_BASE_URL environment variable so
 * developers and CI pipelines can point to any running instance of
 * the application without changing source files.
 *
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests',

  /** Fail the build on CI if any test.only is accidentally left in. */
  forbidOnly: !!process.env.CI,

  /** Retry failed tests once on CI to reduce noise from transient issues. */
  retries: process.env.CI ? 1 : 0,

  /** Limit parallelism on CI; local runs use all available workers. */
  workers: process.env.CI ? 2 : undefined,

  /** Reporter: list output plus an HTML report saved to playwright-report/. */
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
  ],

  use: {
    /** The running application under test. Override via POULE_BASE_URL. */
    baseURL: process.env.POULE_BASE_URL ?? 'http://localhost:8080',

    /** Retain trace on first retry to ease debugging. */
    trace: 'on-first-retry',

    /** Capture screenshot only when a test fails. */
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium-desktop',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox-desktop',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit-mobile',
      use: { ...devices['iPhone 13'] },
    },
    {
      name: 'chromium-tablet',
      use: { ...devices['iPad Pro 11'] },
    },
  ],
});
