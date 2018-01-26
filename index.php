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
            <div class="popover__wrapper align_right no_border">
                <img class="icon" src="./resources/info-xxl.png">                       
                <div class="popover__content">
                    <p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p>
                    <p class="popover__message">Discontinued Lines are only available to order while stocks last.</p>
                    <p class="popover__message">Pre-Order Continuity  lines are not held in stock but are available to order all year round <br> 
                        (The lead time from order to delivery is usually around  14-21 days.)</p>
                    <p class="popover__message">Green – this item is in stock</p>
                    <p class="popover__message">Amber – this item is in stock, but stock levels are low</p>
                    <p class="popover__message">Red – this item is out of stock or sold out</p>
                    <p class="popover__message">Blue – this item is pre-order continuity (available all year) or pre-order fashion</p>
                </div>
            </div>             
        </h1>

        <div id="upload_div" class="file_form_div">
            <form id="upload_file" class="file_form" name="upload_file" method="POST" action="uploadCSV">
                <?php getTables(); ?>
                <input id="MAX_FILE_SIZE" type="hidden" name="MAX_FILE_SIZE" value="100000" />                          
                <input id="import_alter_ego_btn" class="button_class align_right" type="button" name="import_alter_ego_btn" value="Import AlterEgo" />                      
            </form>
            <br/>
        </div>
        <p></p>
        <div id="product_div">
            <h2>Product List</h2>

            <div id="file_form_div" class="file_form_div">          
                <form id="export_file" class="file_form" name="export_file" enctype="multipart/form-data" method="POST" action="download.php">
                    <input id="export_table_name" type="hidden" name="table_name" value="0" />
                    <label for="fileToExport">Choose a CSV file to export to</label>
                    <input id="fileToExport" name="fileToExport" type="file"  />
                    <label id="export_file_name">No File Selected</label>
                    <input id="export_csv_btn" class="button_class align_right" type="button" name= 'export_csv_btn' value="Export CSV" />              
                </form>
            </div>

            <br/>
            <form id="products_form" name="products_form" method="POST" action="showProducts">
                <!--for current_row read group_id-->
                <input id="current_row" type="hidden" name="current_row" value="0" />
                <input id="products_table" type="hidden" name="table_name" value="0" />
                <div id="filters" class="popover_div">
                    <?php generateFilters(); ?>

                </div>
                <input id="next_page_btn" class="button_class align_right"type="button" name= 'next_page_btn' value="Next Page" hidden />
                <input id="prev_page_btn" class="button_class align_right" type="button" name= 'prev_page_btn' value="Previous Page" hidden />
                <select id="items_per_page"  class="align_right"  name="items_per_page">
                    <option name="5_ipp"value="5" selected>5</option>
                    <option name="10_ipp" value="10">10</option>
                    <option name="25_ipp" value="25">25</option>
                    <option name="50_ipp" value="50">50</option>
                </select>
                <p></p>
            </form>
        </div>
    </body>
</html>
