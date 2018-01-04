<?php

if (!include('functions.php')) {
    include ('functions.php');
}
if (!include('db_functions.php')) {
    include ('db_functions.php');
}
if (!include('xml_functions.php')) {
    include ('xml_functions.php');
}

$GLOBALS['res_dir'] = './resources/';
$GLOBALS['res_file'] = './resources/resources.xml';
$GLOBALS['media_dir'] = './media/';
//$GLOBALS['num_rows'] = 5;

if (isset($_POST['action']) && !empty($_POST['action'])) {

    $action = $_POST['action'];
    switch ($action) {
        case 'checkUploadFile' :
            if (doesFileExist(basename($_POST['filename']), TRUE)) {
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

            $start_row = $_POST['current_row'];
            $items_per_page = $_POST['ipp'];
            $filter = $_POST['filter'];
            $html = showProducts($start_row, $items_per_page, $filter);

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);
            echo $json;
            break;

        case 'changeFilter' :

            $start_row = $_POST['current_row'];
            $items_per_page = $_POST['ipp'];
            $filter = $_POST['filter'];
            $html = showProducts($start_row, $items_per_page, $filter);

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);
            echo $json;
            break;

        case 'showTables' :
            echo getTables();
            break;

        case 'alteregoGo': // ***USING***
            $file_name = uploadCSV();
            $result = updateDB();
            
            $start_row = $_POST['current_row'];
            $items_per_page = $_POST['ipp'];
            $filter = $_POST['filter'];
            $html = showProducts($start_row, $items_per_page, $filter);

            $start_row = $_POST['current_row'];

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);

            echo $json;
            break;

//        case 'uploadCSV' : // *** USING ***
//            $file_name = uploadCSV();
//            $headers = getCSVHeaders($file_name);
//            $html = headersToHtml($headers);
//            echo $html;
//            $result = updateDB();
//            break;

        case 'updateDB' : // ***USING***
            $result = updateDB();
            break;

        case 'showProducts' : // ***USING***
            $start_row = $_POST['current_row'];
            $items_per_page = $_POST['ipp'];
            $filter = $_POST['filter'];
            $html = showProducts($start_row, $items_per_page, $filter);

            $start_row = $_POST['current_row'];

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);

            echo $json;
            break;

        case 'nextPage' :
//            getNextPage();
            $items_per_page = $_POST['ipp'];
            $start_row = $_POST['current_row']; // + $items_per_page;
            $filter = $_POST['filter'];
//            $selling_list = json_decode($_POST['selling'], true);
//            updateSelling($selling_list);

            $html = showProducts($start_row, $items_per_page, $filter);

            $start_row = $_POST['current_row'];

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);
            echo $json;
            break;

        case 'previousPage' :
//            getPreviousPage();
            $items_per_page = $_POST['ipp'];
            $start_row = $_POST['current_row'] - $items_per_page;
            $filter = $_POST['filter'];

            $html = showProducts($start_row, $items_per_page, $filter, TRUE);

            $return = array('row' => $start_row, 'html' => $html);
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);
            echo $json;
            break;

        case 'updateSelling' : // ***USING***
            updateSelling();
            break;

        case 'exportCSV' :
            exportToCSV();
            break;
    }
}

