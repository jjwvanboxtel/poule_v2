<?php die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>{TITLE}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="Jaap van Boxtel" />
        <link rel="stylesheet" type="text/css" href="templates/{TEMPLATE_NAME}/template.css" />
        {HEADERS}
    </head>
    <body>
        <div id="container">
           <div id="menu-wrapper">
                <div id="menu">
                    {MENU}
                </div>
                <div id="login">
                    {LOGIN}
                </div>
            </div>
            <div id="header">
                <div id="title">
                    <h1>{TITLE}</h1>
                    <p>{SUB_TITLE}</p>
                </div>
                {LOGO}
            </div>
            <div id="content">
                <div id="column1">
                    <table id="submenu">
                        <tr>
                            <td>{INFORMATION}</td>
                        </tr>
                    </table>
                </div>
                <div id="column2">
                    {CONTENT}
                </div>
            </div>
            <div id="copyright">
                Copyright (c) 2024 vvalem.nl. All rights reserved.
            </div>
        </div>
    </body>
</html>
