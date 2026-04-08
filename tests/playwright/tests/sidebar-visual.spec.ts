import { test, expect } from '@playwright/test';
import { gotoApp } from './helpers/app-fixtures';

/**
 * Visual check for sidebar separator line visibility
 */

test.describe('Visual sidebar check', () => {
  
  test('check sidebar as guest user', async ({ page }) => {
    // Logout first to ensure we're a guest
    await page.goto('/?com=1&option=logout');
    await page.waitForLoadState('domcontentloaded');
    
    // Go to home
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');
    
    // Take screenshot of sidebar
    const sidebar = page.locator('#column1');
    await sidebar.screenshot({ path: 'test-results/sidebar-guest.png' });
    
    // Get submenu element and check for border
    const submenu = page.locator('#submenu');
    
    // Check if submenu is visible
    const isVisible = await submenu.isVisible();
    console.log(`Submenu visible: ${isVisible}`);
    
    // Get innerHTML
    const innerHTML = await submenu.innerHTML();
    console.log('Submenu HTML:', innerHTML.trim());
    
    // Count li items
    const menuItems = submenu.locator('li');
    const itemCount = await menuItems.count();
    console.log(`Menu items: ${itemCount}`);
    
    // Get computed styles
    const styles = await submenu.evaluate((el) => {
      const computed = window.getComputedStyle(el);
      return {
        borderTop: computed.borderTopWidth,
        borderTopStyle: computed.borderTopStyle,
        borderTopColor: computed.borderTopColor,
        paddingTop: computed.paddingTop,
        display: computed.display,
      };
    });
    console.log('Submenu styles:', styles);
    
    // The border should be hidden when there's no menu above
    expect(styles.borderTop).toBe('0px');
  });

  test('check sidebar in competition as guest', async ({ page }) => {
    // Logout and go to competition
    await page.goto('/?com=1&option=logout');
    await page.waitForLoadState('domcontentloaded');
    
    await page.goto('/?competition=1');
    await page.waitForLoadState('domcontentloaded');
    
    // Take screenshot
    const sidebar = page.locator('#column1');
    await sidebar.screenshot({ path: 'test-results/sidebar-guest-competition.png' });
    
    // Check submenu
    const submenu = page.locator('#submenu');
    const innerHTML = await submenu.innerHTML();
    console.log('Submenu in competition:', innerHTML.trim());
    
    const menuItems = submenu.locator('li');
    const itemCount = await menuItems.count();
    console.log(`Menu items in competition: ${itemCount}`);
    
    const borderTop = await submenu.evaluate((el) => {
      return window.getComputedStyle(el).borderTopWidth;
    });
    console.log(`Border top: ${borderTop}`);
  });
});
