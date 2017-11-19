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
$GLOBALS['num_rows'] = 5;

if (isset($_POST['action']) && !empty($_POST['action'])) {

    $action = $_POST['action'];
    switch ($action) {
        case 'checkFile' :
            if (doesFileExist(basename($_POST['filename']))) {
                echo 'true';
            }
            break;

        case 'changeIPP' :
            
            $row = $_POST['current_row'];
            $GLOBALS['num_rows'] = $_POST['ipp'];
            $html = showProducts($row);
            
            $return = array('row' => $row, 'html' => $html);            
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);     
            echo $json;
//            echo $html;
            break;

        case 'showTables' :
            echo getTables();
            break;

        case 'useFile':
            $result = useCSV();
            echo $result;
            break;

        case 'uploadCSV' :
            $file_name = uploadCSV();
            $headers = getCSVHeaders($file_name);
            $html = headersToHtml($headers);
            echo $html;
            break;

        case 'updateDB' :
            $result = updateDB();
            break;

        case 'showProducts' :
            $row = $_POST['current_row'];
            $html = showProducts($row);
            
            $return = array('row' => $row, 'html' => $html);            
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);     
            echo $json;
            break;

        case 'nextPage' :
//            getNextPage();
            $row = $_POST['current_row'] + $GLOBALS['num_rows'];
            $html = showProducts($row);
            
            $return = array('row' => $row, 'html' => $html);            
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);     
            echo $json;
//            echo $html;
            break;

        case 'previousPage' :
//            getPreviousPage();
            $row = $_POST['current_row'] - $GLOBALS['num_rows'];
            $html = showProducts($row);
            
            $return = array('row' => $row, 'html' => $html);            
            $json = json_encode($return, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS);     
            echo $json;
//            echo $html;
            break;
    }
}

