import { test, expect } from '@playwright/test';
import { gotoApp } from './helpers/app-fixtures';
import { homeRoute } from './helpers/navigation-data';

/**
 * Test for sidebar separator visibility
 * 
 * The separator line (border-top) in #submenu should only be visible
 * when there are actual menu items to display.
 */

test.describe('Sidebar separator line', () => {
  
  test('separator should NOT be visible when user is not logged in (no submenu items)', async ({ page }) => {
    // Navigate to home page as guest (not logged in)
    await gotoApp(page, homeRoute());
    
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Get the submenu element
    const submenu = page.locator('#submenu');
    
    // Check if submenu exists
    await expect(submenu).toBeAttached();
    
    // Check if submenu has any li elements
    const menuItems = submenu.locator('li');
    const itemCount = await menuItems.count();
    
    console.log(`Found ${itemCount} menu items in submenu`);
    
    // Get computed border-top style
    const borderTop = await submenu.evaluate((el) => {
      return window.getComputedStyle(el).borderTopWidth;
    });
    
    console.log(`Border-top width: ${borderTop}`);
    
    // If there are no menu items, border should be 0px or none
    if (itemCount === 0) {
      expect(borderTop).toBe('0px');
    }
  });
  
  test('separator SHOULD be visible when there are submenu items', async ({ page }) => {
    // This test would require login and competition selection
    // For now, we'll skip it if we can't access it
    
    // Try to navigate to a competition page
    await page.goto('/?competition=1');
    await page.waitForLoadState('domcontentloaded');
    
    const submenu = page.locator('#submenu');
    const menuItems = submenu.locator('li');
    const itemCount = await menuItems.count();
    
    console.log(`Found ${itemCount} menu items in submenu (with competition)`);
    
    // Skip test if no items found (might not have test data)
    if (itemCount === 0) {
      test.skip();
      return;
    }
    
    // Get computed border-top style
    const borderTop = await submenu.evaluate((el) => {
      return window.getComputedStyle(el).borderTopWidth;
    });
    
    console.log(`Border-top width with items: ${borderTop}`);
    
    // Border should be visible (1px or more)
    expect(borderTop).not.toBe('0px');
  });

  test('inspect submenu HTML structure when no items', async ({ page }) => {
    await gotoApp(page, homeRoute());
    await page.waitForLoadState('domcontentloaded');
    
    const submenu = page.locator('#submenu');
    
    // Get innerHTML to see what's actually in there
    const innerHTML = await submenu.innerHTML();
    console.log('Submenu innerHTML:', innerHTML.trim());
    
    // Get all child elements
    const children = submenu.locator('> *');
    const childCount = await children.count();
    console.log(`Direct children in submenu: ${childCount}`);
    
    for (let i = 0; i < childCount; i++) {
      const child = children.nth(i);
      const tagName = await child.evaluate(el => el.tagName);
      const childHTML = await child.evaluate(el => el.outerHTML);
      console.log(`Child ${i}: ${tagName}`, childHTML.substring(0, 200));
    }
    
    // Check if the UL is empty (no li elements)
    const menuItems = submenu.locator('li');
    const itemCount = await menuItems.count();
    console.log(`Total li elements: ${itemCount}`);
    
    // If UL is empty, check border
    const hasEmptyUL = childCount > 0 && itemCount === 0;
    console.log(`Has empty UL tag: ${hasEmptyUL}`);
    
    if (hasEmptyUL) {
      const borderTop = await submenu.evaluate((el) => {
        return window.getComputedStyle(el).borderTopWidth;
      });
      console.log(`Border when UL is empty: ${borderTop}`);
      expect(borderTop).toBe('0px');
    }
  });
});
