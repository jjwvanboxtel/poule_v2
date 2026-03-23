/**
 * Sidebar toggle script for poule_v2 orange template.
 *
 * Handles:
 *   - Desktop: collapse / expand sidebar and shift topbar + main wrapper
 *   - Mobile:  slide sidebar in/out with overlay backdrop
 *
 * IDs expected in the DOM:
 *   #column1        - the sidebar element
 *   #menu-wrapper   - the fixed topbar nav
 *   #container      - the main content wrapper
 *   #overlay        - the mobile backdrop overlay
 *   #toggleBtn      - desktop collapse/expand button (hidden on mobile)
 *   #mobileBtn      - mobile open button (hidden on desktop)
 */
(function () {
  'use strict';

  var sidebar    = document.getElementById('column1');
  var topbar     = document.getElementById('menu-wrapper');
  var mainWrap   = document.getElementById('container');
  var overlay    = document.getElementById('overlay');
  var toggleBtn  = document.getElementById('toggleBtn');
  var mobileBtn  = document.getElementById('mobileBtn');

  // Guard: if neither the sidebar nor the topbar is present there is nothing
  // to manage — skip initialisation entirely to avoid spurious JS errors.
  if (!sidebar && !topbar) { return; }

  // ---- Desktop: collapse / expand ----
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      if (sidebar)  sidebar.classList.toggle('collapsed');
      if (topbar)   topbar.classList.toggle('full');
      if (mainWrap) mainWrap.classList.toggle('full');
    });
  }

  // ---- Mobile: open sidebar ----
  if (mobileBtn) {
    mobileBtn.addEventListener('click', function () {
      if (sidebar) sidebar.classList.add('mobile-show');
      if (overlay) overlay.classList.add('show');
    });
  }

  // ---- Mobile: click overlay to close sidebar ----
  if (overlay) {
    overlay.addEventListener('click', function () {
      if (sidebar) sidebar.classList.remove('mobile-show');
      overlay.classList.remove('show');
    });
  }
}());
