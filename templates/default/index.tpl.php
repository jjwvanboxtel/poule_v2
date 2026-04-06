<?php die(); ?>
<html>
    <head>
        <title>{TITLE}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="MI3TIa" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
        <link rel="stylesheet" type="text/css" href="templates/{TEMPLATE_NAME}/template.css" />
        {HEADERS}
    </head>
    <body>
         
        <div id="container">
            <div id="menucontainer">
               <div id="menu">
                    <h1>{LANG_MENU_HEADER}</h1>

                    <ul>
                    {MENU}
                    </ul>

                    <ul>
                    {INLOGGEN}
                    </ul>
                </div>
            </div>
            
            <div id="content">
                {CONTENT}
            </div>
            
        </div>
  
    </body>
</html>
