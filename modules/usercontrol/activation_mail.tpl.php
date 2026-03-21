<?php die(); ?>

<html>
    <head>
        <style>
        body {
            font-family: "Georgia Ref", "Trebuchet MS", "Verdana";
        }

        table {
            border: 1px solid black;
        }
        
        table.list tr.even {
            background-color: #eee;
        }
        
        table.list tr.odd {
            background-color: white;
        }
        
        table.list tr.time {
            background-color: red;
            color: white;
        }

        table.list {
            font-size: 12px;
            color: black;
            width: 100%;
        }

        table.list th {
            text-align: left;
            padding: 2px;
        }

        table.list td {
            padding: 2px;
        }

        table.list tr.hover {
            background-color: #a0d6ed;
        }

        table.list a {
            color: blue;
        }
        </style>
    </head>
    <body>

    {CONTENT}

    </body>
</html>