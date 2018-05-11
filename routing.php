<?php

if ( ! include('functions.php') ) {
        include ('functions.php');
}
if ( ! include('db_functions.php') ) {
        include ('db_functions.php');
}
if ( ! include('xml_functions.php') ) {
        include ('xml_functions.php');
}

$GLOBALS[ 'res_dir' ] = './resources/';
$GLOBALS[ 'res_file' ] = './resources/resources.xml';
$GLOBALS[ 'media_dir' ] = './media/';
$GLOBALS[ 'tablename' ] = 'stocklines';
$GLOBALS[ 'filepath' ] = 'C:/wamp64/www/StockPicker/resources/';
$GLOBALS[ 'numproducts' ] = 0;
//$GLOBALS['num_rows'] = 5;

if ( isset ( $_POST[ 'action' ] ) && ! empty ( $_POST[ 'action' ] ) ) {

        $action = $_POST[ 'action' ];
        switch ( $action ) {
                case 'checkUploadFile' :
                        if ( doesFileExist ( basename ( $_POST[ 'filename' ] ), TRUE ) ) {
                                echo 'true';
                        }
                        break;

                case 'checkExportFile' :
//            if (doesFileExist(basename($_POST['filename']), FALSE)) {
                        echo 'true';
//            } else {
//                echo 'false';
//            }
                        break;

                case 'changeIPP' :

                        $start_row = $_POST[ 'current_row' ];
                        $items_per_page = $_POST[ 'ipp' ];
                        $filters = $_POST[ 'filter' ];
                        $filters = explode ( ',', $_POST[ 'filter' ] );
                        $html = showProducts ( $start_row, $items_per_page, $filters );

                        $return = array ( 'row' => $start_row, 'html' => "<div>Test</div>" );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );

                        $return = array ( 'row' => $start_row, 'html' => $html );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );
                        echo $json;
                        break;

                case 'changeFilter' :

                        $start_row = $_POST[ 'current_row' ];
                        $items_per_page = $_POST[ 'ipp' ];
                        $filters = $_POST[ 'filter' ];
                        $filters = explode ( ',', $_POST[ 'filter' ] );
                        
                        createFilteredTable();
                        $html = showProducts ( $start_row, $items_per_page, $filters );

                        $return = array ( 'row' => $start_row, 'html' => "<div>Test</div>" );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );

                        // add $GLOBALS[ 'numproducts' ] to return and display at top
                        $return = array ( "row" => $start_row, "html" => $html );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );
                        echo $json;
                        break;

                case 'showTables' :
                        echo getTables ();
                        break;

                case 'importAlterEgo': // ***USING***
                        if ( empty ( basename ( $_FILES[ 'uploadedfile' ][ 'name' ] ) ) ) {
                                break;
                        }
                        $file_name = importCSV ();
                        $conn = openDB ( 'rwk_productchooserdb' );

                        // create main table
                        createMainTable ( $conn, $file_name );
                        // load in file to main table
                        loadCSVData ( $conn, $file_name );
                        // create supporting tables
//                        createGroupsTable ( $conn );
                        createCategoriesTable ( $conn );
                        createBrandsTable ( $conn );
                        createExtraTable ( $conn );
                        createImagesTable ( $conn );
                        // rupdate the support tables just created
                        updateSupportTables ( $conn );

//                        bulkFillTable($conn, $file_name);

                        $start_row = $_POST[ 'current_row' ];
                        $items_per_page = $_POST[ 'ipp' ];
                        $filters = explode ( ',', $_POST[ 'filter' ] );
                        $html = showProducts ( $start_row, $items_per_page, $filters );
                        $filters = appendFilters ();

                        $return = array ( 'row' => $start_row, 'html' => $html, 'filters' => $filters );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );

                        echo $json;
                        break;

                case 'updateDB' : // ***USING***
                        $result = updateDB ();
                        break;

                case 'showProducts' : // ***USING***
                        $start_row = $_POST[ 'current_row' ];
                        $items_per_page = $_POST[ 'ipp' ];
                        $filters = explode ( ',', $_POST[ 'filter' ] );
                        if ( ! isset ( $_SESSION ) ) {
                                session_start ();
                        }
                        $_SESSION[ 'table_name' ] = $_POST[ 'table_name' ];
                        $html = showProducts ( $start_row, $items_per_page, $filters );
                        $filters = appendFilters ();

                        $return = array ( 'row' => $start_row, 'html' => $html, 'filters' => $filters );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );
                        $error = json_last_error ();
                        echo $json;
                        break;

                case 'nextPage' :
                        $items_per_page = $_POST[ 'ipp' ];
                        $start_row = $_POST[ 'current_row' ] + $items_per_page;
                        $filters = explode ( ',', $_POST[ 'filter' ] );

                        $html = showProducts ( $start_row, $items_per_page, $filters );
                        $return = array ( 'row' => $start_row, 'html' => $html );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );
                        echo $json;
                        break;

                case 'previousPage' :
                        $items_per_page = $_POST[ 'ipp' ];
                        $start_row = $_POST[ 'current_row' ] - $items_per_page;
                        $filters = explode ( ',', $_POST[ 'filter' ] );

                        $html = showProducts ( $start_row, $items_per_page, $filters, TRUE );

                        $return = array ( 'row' => $start_row, 'html' => $html );
                        $json = json_encode ( $return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS );
                        echo $json;
                        break;

                case 'updateSelling' :
                        updateSelling ();
                        break;

                case 'exportCSV' :
                        exportToCSV ( 'alterego' );
                        break;
        }
}

