<?php die(); ?>
<!DOCTYPE html>
<html lang="nl">
    <head>
        <title>{TITLE}</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="author" content="Jaap van Boxtel" />
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="templates/{TEMPLATE_NAME}/template.css" />
        {HEADERS}
    </head>
    <body>
        <div id="container">
            <nav id="menu-wrapper">
                <div id="menu">
                    {MENU}
                </div>
                <div id="login">
                    {LOGIN}
                </div>
            </nav>
            <div id="header">
                <div id="title">
                    <h1>{TITLE}</h1>
                    <p>{SUB_TITLE}</p>
                </div>
                {LOGO}
            </div>
            <div id="content">
                <div id="column1">
                    <div id="submenu">
                        {INFORMATION}
                    </div>
                </div>
                <div id="column2">
                    {CONTENT}
                </div>
            </div>
            <div id="copyright">
                Copyright &copy; 2024 vvalem.nl. All rights reserved.
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
