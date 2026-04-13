import { test, expect } from '@playwright/test';

/**
 * Test to verify sidebar separator appears correctly
 * when user is logged in as admin
 */

test.describe('Sidebar separator with admin user', () => {
  
  test('separator SHOULD be visible when admin user is logged in with competition selected', async ({ page }, testInfo) => {
    // Use the stored auth state (admin login from global setup)
    
    // Navigate to a competition page
    await page.goto('/?competition=1');
    await page.waitForLoadState('domcontentloaded');
    
    // Take screenshot
    const sidebar = page.locator('#column1');
    await sidebar.screenshot({ path: 'test-results/sidebar-admin-competition.png' });
    
    // Get submenu element
    const submenu = page.locator('#submenu');
    
    // Check for menu items in #menu
    const menu = page.locator('#column1 #menu');
    const menuItems = menu.locator('li');
    const menuItemCount = await menuItems.count();
    console.log(`Admin menu items: ${menuItemCount}`);
    
    // Check for items in submenu
    const submenuItems = submenu.locator('li');
    const submenuItemCount = await submenuItems.count();
    console.log(`Submenu items: ${submenuItemCount}`);
    
    // Get border style
    const borderTop = await submenu.evaluate((el) => {
      return window.getComputedStyle(el).borderTopWidth;
    });
    console.log(`Border when logged in as admin: ${borderTop}`);
    
    // If there are menu items above AND submenu items, border should be visible
    if (menuItemCount > 0 && submenuItemCount > 0) {
      // Border should be 1px (visible)
      expect(borderTop).not.toBe('0px');
      console.log('✓ Border is correctly visible when both menus have items');
    } else {
      console.log(`Skipping assertion: menuItems=${menuItemCount}, submenuItems=${submenuItemCount}`);
    }
  });
});
