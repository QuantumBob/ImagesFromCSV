<!DOCTYPE html>
<?php
session_start();
include 'main.php';
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
        <h1>
            <input id="home_btn" class="button_class spacer-right" type="button" name= 'home_btn' value="Home" />
            Product Importer
        </h1>
        
        <div id="upload_div" class="file_form_div">
            <form id="upload_file" class="file_form" name="upload_file" method="POST" action="uploadCSV">
                <input id="MAX_FILE_SIZE" type="hidden" name="MAX_FILE_SIZE" value="100000" />                          
                <input id="alter_ego_go_btn" class="button_class align_right" type="button" name="alter_ego_go_btn" value="AlterEgo Go" />                      
            </form>
            <br/>
            <form id="existing_files_form" name="existing_files_form" method="POST" action="useFile">
                <label id="exisiting_file_lbl" >Use Existing File</label>
                <?php getFileList(); ?>
                <br />
                <input id="use_existing_file_btn" class="button_class align_right" type="button" name= 'submit' value="Use Existing" hidden/>
            </form>
        </div>
        <p></p>
        <div id="product_div">
            <h2>Product List</h2>
            <div id="file_form_div" class="file_form_div" hidden>           
                <form id="export_file" class="file_form" name="export_file" enctype="multipart/form-data" method="POST" action="download.php">
                    <input id="export_table_name" type="hidden" name="table_name" value="0" />
                    <label for="fileToExport">Choose a CSV file to export to</label>
                    <input id="fileToExport" name="fileToExport" type="file"  />
                    <label id="export_file_name">No File Selected</label>
                    <input id="export_csv_btn" class="button_class align_right" type="button" name= 'export_csv_btn' value="Export CSV" />              
                </form>
            </div>
            <form id="products_form" name="products_form" method="POST" action="showProducts">
                <!--for current_row read group_id-->
                <input id="current_row" type="hidden" name="current_row" value="0" />
                <input id="products_table" type="hidden" name="table_name" value="0" />
                <input id="next_page_btn" class="button_class align_right"type="button" name= 'next_page_btn' value="Next Page" hidden />
                <input id="prev_page_btn" class="button_class align_right" type="button" name= 'prev_page_btn' value="Previous Page" hidden />
                <select id="items_per_page"  class="align_right"  name="items_per_page">
                    <option name="5_ipp"value="5" selected>5</option>
                    <option name="10_ipp" value="10">10</option>
                    <option name="25_ipp" value="25">25</option>
                    <option name="50_ipp" value="50">50</option>
                </select>               
                <?php generateFilters(); ?>
                <p></p>
            </form>
        </div>
    </body>
</html>
