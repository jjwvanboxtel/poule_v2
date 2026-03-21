/**
 * Viewport utilities for poule_v2 Playwright UI regression tests.
 *
 * Provides named viewport presets that map to the three primary breakpoints
 * used when validating responsive layout:
 *   - Desktop  (≥ 1280 px wide)
 *   - Tablet   (768 px – 1279 px)
 *   - Mobile   (< 768 px)
 *
 * Usage in a spec:
 *   import { VIEWPORTS, viewportLabel } from '../helpers/viewports';
 *
 *   for (const [label, size] of Object.entries(VIEWPORTS)) {
 *     test(`navigation is visible on ${label}`, async ({ page }) => {
 *       await page.setViewportSize(size);
 *       // … assertions …
 *     });
 *   }
 */

/** A Playwright-compatible viewport size object. */
export interface ViewportSize {
  width: number;
  height: number;
}

/** Named viewport presets covering desktop, tablet and mobile breakpoints. */
export const VIEWPORTS = {
  /** Full desktop — 1440 × 900 px */
  desktop: { width: 1440, height: 900 },

  /** Large desktop — 1920 × 1080 px */
  desktopLarge: { width: 1920, height: 1080 },

  /** Tablet landscape — 1024 × 768 px */
  tabletLandscape: { width: 1024, height: 768 },

  /** Tablet portrait — 768 × 1024 px */
  tabletPortrait: { width: 768, height: 1024 },

  /** Mobile large — 430 × 932 px (iPhone 14 Pro Max) */
  mobileLarge: { width: 430, height: 932 },

  /** Mobile standard — 390 × 844 px (iPhone 14) */
  mobile: { width: 390, height: 844 },

  /** Mobile small — 375 × 667 px (iPhone SE) */
  mobileSmall: { width: 375, height: 667 },
} as const satisfies Record<string, ViewportSize>;

/** Union type of all known viewport preset names. */
export type ViewportName = keyof typeof VIEWPORTS;

/**
 * Return the human-readable label for a viewport preset name,
 * suitable for use in test titles.
 *
 * @example viewportLabel('tabletPortrait') // → 'tablet portrait (768×1024)'
 */
export function viewportLabel(name: ViewportName): string {
  const { width, height } = VIEWPORTS[name];
  const readable = name.replace(/([A-Z])/g, ' $1').toLowerCase();
  return `${readable} (${width}×${height})`;
}

/**
 * Return the subset of viewport presets that fall into the given category.
 *
 * @param category  'desktop' | 'tablet' | 'mobile'
 */
export function viewportsFor(
  category: 'desktop' | 'tablet' | 'mobile',
): Partial<typeof VIEWPORTS> {
  const map: Record<string, ViewportName[]> = {
    desktop: ['desktop', 'desktopLarge'],
    tablet: ['tabletLandscape', 'tabletPortrait'],
    mobile: ['mobileLarge', 'mobile', 'mobileSmall'],
  };

  return Object.fromEntries(
    (map[category] ?? []).map((name) => [name, VIEWPORTS[name]]),
  ) as Partial<typeof VIEWPORTS>;
}
