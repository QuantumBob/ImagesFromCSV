<?php
session_start();
include 'routing.php';
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="imagesfromcsv.css" />
        <script src="jquery-3.2.1.js"></script>
        <script src="scripts.js"></script>
        <title></title>
    </head>
    <body>
        <div id="file_form_div" class="file_form_div">          
            <form id="get_dir" class="file_form" name="get_dir"enctype="multipart/form-data" method="POST" action= "get_dir.php">
                <input id="array_index" type="hidden" name="array_index" value="0" />
                <label for="dir_to_check">Choose a directory to check</label>
                <input id="dir_to_check" name="dir_to_check" type="file"  />
                <label id="export_file_name">No File Selected</label>
                <input id="dir_to_check_btn" class="button_class align_right" type="submit" name= 'dir_to_check_btn' value="Check Dir" />              
            </form>
        </div>

        <div>
            <input id="alterego_page" class="button_class align_right" type="button" name= 'alterego_page' value="alterego page" />     
        </div>
    </body>
</html>
