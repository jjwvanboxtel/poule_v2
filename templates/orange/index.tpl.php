<?php die(); ?>
<!DOCTYPE html>
<html lang="nl">
    <head>
        <title>{TITLE}</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="author" content="Jaap van Boxtel" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.35.0/dist/tabler-icons.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="templates/{TEMPLATE_NAME}/template.css" />
        {HEADERS}
    </head>
    <body>
        <!-- Mobile overlay backdrop -->
        <div id="overlay" class="overlay"></div>

        <!-- ===================== TOPBAR ===================== -->
        <nav id="menu-wrapper" class="topbar navbar bg-white border-bottom fixed-top px-3">
            <!-- Desktop: collapse/expand sidebar -->
            <button id="toggleBtn"
                    class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm me-2"
                    type="button"
                    aria-label="Sidebar in-/uitklappen">
                <i class="ti ti-layout-sidebar-left-expand"></i>
            </button>
            <!-- Mobile: open sidebar -->
            <button id="mobileBtn"
                    class="btn btn-light btn-icon btn-sm d-lg-none me-2"
                    type="button"
                    aria-label="Menu openen">
                <i class="ti ti-menu-2"></i>
            </button>
            <!-- App title shown in topbar on mobile / small screens -->
            <span class="navbar-brand fw-semibold d-lg-none">{TITLE}</span>

            <!-- Main navigation menu -->
            <div id="menu" class="d-none d-lg-flex ms-auto">
                {MENU}
            </div>

            <!-- Login / logout area -->
            <div id="login">
                {LOGIN}
            </div>
        </nav>

        <!-- ===================== SIDEBAR ===================== -->
        <div id="column1" class="sidebar">
            <!-- Logo / brand area (same height as topbar) -->
            <div class="sidebar-logo">
                <a href="." aria-label="{TITLE}">
                    <i class="ti ti-trophy sidebar-icon"></i>
                    <span class="sidebar-brand-text">{TITLE}</span>
                </a>
            </div>

            <!-- Primary navigation (mobile only) -->
            <div id="menu-mobile" class="d-lg-none">
                {MENU}
            </div>

            <!-- Contextual submenu (competition info / sub-links) -->
            <div id="submenu">
                {INFORMATION}
            </div>
        </div>

        <!-- ===================== MAIN WRAPPER ===================== -->
        <div id="container" class="main-wrapper">

            <!-- Hero / page header -->
            <div id="header">
                <div id="title">
                    <h1>{TITLE}</h1>
                    <p>{SUB_TITLE}</p>
                </div>
                {LOGO}
            </div>

            <!-- Primary content -->
            <div id="column2">
                {CONTENT}
            </div>

            <!-- Footer -->
            <div id="copyright">
                Copyright &copy; 2024 vvalem.nl. All rights reserved.
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="templates/{TEMPLATE_NAME}/sidebar.js"></script>
    </body>
</html>
