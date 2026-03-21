import { type Locator, type Page } from '@playwright/test';

/**
 * DOM selector constants and Playwright locator helpers for poule_v2 UI tests.
 *
 * Organises selectors into three categories that map to the three primary
 * screen types in the application:
 *
 *   1. Shell selectors    — container, topbar, navigation, header, sidebar
 *   2. Form selectors     — form elements, labels, submit/cancel buttons
 *   3. Overview selectors — list tables, table headers, action links
 *
 * Additionally, message/alert selectors cover the Bootstrap-aligned utility
 * classes added to `templates/orange/template.css` (T006).
 *
 * Usage:
 *   import { SHELL_SELECTORS, getNavLinks } from '../helpers/module-routes';
 */

// ---------------------------------------------------------------------------
// Shell selectors
// ---------------------------------------------------------------------------

/**
 * Selectors for the application shell structure rendered by
 * `templates/orange/index.tpl.php`.
 */
export const SHELL_SELECTORS = {
  /** Outermost layout wrapper — `#container`. */
  container: '#container',

  /** Navigation bar wrapper — `#menu-wrapper`. */
  menuWrapper: '#menu-wrapper',

  /** Primary navigation list — `#menu`. */
  menu: '#menu',

  /** Login/logout nav area — `#login`. */
  loginNav: '#login',

  /** Page header / hero band — `#header`. */
  header: '#header',

  /** Title text block inside the header — `#header #title`. */
  headerTitle: '#header #title',

  /** Primary page title (`<h1>`) inside the header. */
  h1: '#header h1',

  /** Subtitle / competition name beneath the h1. */
  subTitle: '#header p',

  /** Content wrapper — `#content`. */
  content: '#content',

  /** Sidebar / contextual info column — `#content #column1`. */
  column1: '#content #column1',

  /** Contextual submenu inside the sidebar — `#submenu`. */
  sidebar: '#submenu',

  /** Primary content column — `#content #column2`. */
  column2: '#content #column2',

  /** Footer copyright bar — `#copyright`. */
  copyright: '#copyright',

  /** Active/current navigation item — `.current_page_item`. */
  activeNavItem: '#menu .current_page_item',

  /** Anchor inside the active nav item. */
  activeNavLink: '#menu .current_page_item a',

  /** All navigation anchors in the primary menu. */
  navLinks: '#menu a',

  /** All anchors in the login/logout nav area. */
  loginNavLinks: '#login a',

  /** Competition logo image inside the header. */
  logo: '#header img.logo',
} as const;

// ---------------------------------------------------------------------------
// Title panel selectors
// ---------------------------------------------------------------------------

/**
 * Selectors for page-level title panels.
 *
 * The application wraps screen headings in `<div class="title"><h2>…</h2></div>`.
 * The Bootstrap-aligned utility layer (T006) also provides `.page-title` and
 * `.card-title` for future use by the refactored shell and module templates.
 */
export const TITLE_SELECTORS = {
  /** Legacy title panel wrapper — `.title`. */
  titlePanel: '.title',

  /** Heading inside a legacy title panel — `.title h2`. */
  titleHeading: '.title h2',

  /** Bootstrap-aligned page title — `.page-title`. */
  pageTitle: '.page-title',

  /** Bootstrap-aligned card title — `.card-title`. */
  cardTitle: '.card-title',
} as const;

// ---------------------------------------------------------------------------
// Form selectors
// ---------------------------------------------------------------------------

/**
 * Selectors for form elements.
 *
 * All forms in the application post to the current URL (empty `action`),
 * use `method="post"`, and embed field rows in a `table.list`.
 */
export const FORM_SELECTORS = {
  /** Any `<form>` element on the page. */
  form: 'form',

  /** The list-style table used to lay out form fields. */
  formTable: 'form table.list',

  /** Generic text input inside a form. */
  textInput: 'form input[type="text"]',

  /** Password input inside a form. */
  passwordInput: 'form input[type="password"]',

  /** Email input inside a form. */
  emailInput: 'form input[type="email"]',

  /** The primary submit button (`name="submit"`). */
  submitButton: 'input[type="submit"][name="submit"]',

  /** Cancel / back button rendered as `type="button"`. */
  cancelButton: 'input[type="button"]',

  /** Any `<select>` inside a form. */
  selectField: 'form select',

  /** Any `<textarea>` inside a form. */
  textArea: 'form textarea',

  /**
   * Any field in an error state.
   * The app applies the `.error` class to `input`, `select`, and `textarea`
   * on validation failure.
   */
  errorField: 'input.error, select.error, textarea.error',

  /** Bootstrap-aligned form label — `.form-label`. */
  formLabel: '.form-label',

  /** Bootstrap-aligned form control — `.form-control`. */
  formControl: '.form-control',

  /** Bootstrap-aligned form group — `.form-group`. */
  formGroup: '.form-group',

  /** Bootstrap-aligned action row at the bottom of forms — `.form-actions`. */
  formActions: '.form-actions',
} as const;

// ---------------------------------------------------------------------------
// Overview / list table selectors
// ---------------------------------------------------------------------------

/**
 * Selectors for overview and list table screens.
 *
 * The application renders list screens with a `<table class="list">` element.
 * Rows are alternately marked `tr.even` / `tr.odd` by the module classes.
 */
export const OVERVIEW_SELECTORS = {
  /** The list/overview table — `table.list`. */
  listTable: 'table.list',

  /** Table header cells — `table.list th`. */
  tableHeader: 'table.list th',

  /** All table rows (including the header row). */
  tableRow: 'table.list tr',

  /** Even-numbered data rows — `table.list tr.even`. */
  evenRow: 'table.list tr.even',

  /** Odd-numbered data rows — `table.list tr.odd`. */
  oddRow: 'table.list tr.odd',

  /** Any anchor inside the list table (action links). */
  actionLink: 'table.list a',

  /** Bootstrap-aligned responsive table wrapper — `.table-responsive`. */
  tableResponsive: '.table-responsive',

  /** Bootstrap-aligned overview table — `.overview-table`. */
  overviewTable: '.overview-table',

  /** Action row at the bottom of an overview (add button row). */
  actionRow: '.action-row',
} as const;

// ---------------------------------------------------------------------------
// Alert / message selectors
// ---------------------------------------------------------------------------

/**
 * Selectors for status messages and alerts.
 *
 * These map to the Bootstrap-aligned utility classes defined in T006 and to
 * the legacy inline message containers already present in the templates.
 */
export const MESSAGE_SELECTORS = {
  /** Any Bootstrap-aligned alert element. */
  alert: '.alert',

  /** Success alert — `.alert-success`. */
  successAlert: '.alert-success',

  /** Danger / error alert — `.alert-danger`. */
  dangerAlert: '.alert-danger',

  /** Warning alert — `.alert-warning`. */
  warningAlert: '.alert-warning',

  /** Info alert — `.alert-info`. */
  infoAlert: '.alert-info',
} as const;

// ---------------------------------------------------------------------------
// Playwright locator helpers
// ---------------------------------------------------------------------------

/**
 * Return a Locator for the primary navigation menu.
 *
 * @param page  The Playwright Page object.
 */
export function getNavMenu(page: Page): Locator {
  return page.locator(SHELL_SELECTORS.menu);
}

/**
 * Return a Locator for the active navigation item.
 *
 * @param page  The Playwright Page object.
 */
export function getActiveNavItem(page: Page): Locator {
  return page.locator(SHELL_SELECTORS.activeNavItem);
}

/**
 * Return a Locator for the contextual sidebar / submenu.
 *
 * @param page  The Playwright Page object.
 */
export function getSidebar(page: Page): Locator {
  return page.locator(SHELL_SELECTORS.sidebar);
}

/**
 * Return a Locator for the primary content column.
 *
 * @param page  The Playwright Page object.
 */
export function getContentColumn(page: Page): Locator {
  return page.locator(SHELL_SELECTORS.column2);
}

/**
 * Return a Locator for the page heading — falling back through multiple
 * selectors used across the application and its refactored utility layer.
 *
 * @param page  The Playwright Page object.
 */
export function getPageHeading(page: Page): Locator {
  return page
    .locator(
      [
        'h1',
        TITLE_SELECTORS.titleHeading,
        TITLE_SELECTORS.pageTitle,
        TITLE_SELECTORS.cardTitle,
      ].join(', '),
    )
    .first();
}

/**
 * Return a Locator for the list/overview table.
 *
 * @param page  The Playwright Page object.
 */
export function getListTable(page: Page): Locator {
  return page.locator(OVERVIEW_SELECTORS.listTable);
}

/**
 * Return a Locator for the primary form submit button.
 *
 * @param page  The Playwright Page object.
 */
export function getSubmitButton(page: Page): Locator {
  return page.locator(FORM_SELECTORS.submitButton);
}

/**
 * Return a Locator for any alert currently visible on the page.
 *
 * @param page  The Playwright Page object.
 */
export function getAlert(page: Page): Locator {
  return page.locator(MESSAGE_SELECTORS.alert);
}
