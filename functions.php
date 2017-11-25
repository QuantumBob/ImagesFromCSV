<?php

function getFileList() {

    $target_dir = $GLOBALS['res_dir'];
    if ($handle = opendir($target_dir)) {
        while (FALSE !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                if (stripos($entry, '.csv') !== FALSE) {
                    $array[] = $entry;
                }
            }
        }
        closedir($handle);
        foreach ($array as $file) {
            $html[] = "<input id='$file' class='button_class gen_btn'  type='button' name= '$file' value='$file' />";
        }
        echo implode(' ', $html);
    }
}

function doesFileExist($file_name, $upload) {

    if ($upload) {
        $target_path = $GLOBALS['res_dir'] . $file_name;

        if (file_exists($target_path)) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        if (($handle = fopen("$file_name", "x")) !== FALSE) {
            fclose($handle);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function useCSV() {

    $target_path = $GLOBALS['res_dir'];
    if (isset($_FILES['uploadedfile']) && !empty($_FILES['uploadedfile'])) {
        $file_name = basename($_FILES['uploadedfile']['name']);
    } else if (isset($_POST['filename']) && !empty($_POST['filename'])) {
        $file_name = $_POST['filename'];
    } else {
        die();
    }

    $target_path = $target_path . $file_name;

    $headers = getCSVHeaders($file_name);
    $str = headersToHtml($headers);
    session_start();
    $_SESSION["filename"] = $file_name;

    return $str;
}

function uploadCSV($file_name) {

    if (empty($file_name)) {
        return FALSE;
    }
    $target_path = $GLOBALS['res_dir'];
    $file_name = basename($_FILES['uploadedfile']['name']);

    $target_path = $target_path . $file_name;

    if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
//        $headers = getCSVHeaders($file_name);
//        $str = headersToHtml($headers);
        session_start();
        $_SESSION["filename"] = $file_name;
//        return $str;
        return $file_name;
    } else {
        exit("There was an error uploading the file, please try again!");
    }
}

function getCSVHeaders($file_name) {

    $resources_dir = $GLOBALS['res_dir'];
    $file_url = $resources_dir . $file_name;

    if (($handle = fopen("$file_url", "r")) !== FALSE) {

        $headers = fgetcsv($handle, 1000, ",");
        fclose($handle);
    }
    return $headers;
}

function headersToHtml($headers) {

    $html_array[] = '<div class="checkbox_container">';
    foreach ($headers as $header) {
        $html_array[] = '<div class="input_class"><input type="checkbox" class="input_label" id="' . $header . '" name="' . (string) $header . '" value="' . (string) $header . '"><label for="' . $header . '">' . (string) $header . '</div>';
    }
    $html_array[] = '</div>';
    return implode(" ", $html_array);
}

function headersToHtml2($headers) {

    $html_array[] = '<div class="checkbox_container">';
    foreach ($headers as $header) {
        $html_array[] = '<div class="input_class"><input type="checkbox" class="input_label" id="' . $header . '" name="' . (string) $header . '" value="' . (string) $header . '"><label for="' . $header . '">' . (string) $header . '</div>';
    }
    $html_array[] = '</div>';
    return implode(" ", $html_array);
}

// parses csv and inputs data into main table and groups table
function populateTableFromCSV($conn, $file_name, $create_groups = TRUE) {

    $resources_dir = $GLOBALS['res_dir'];
    $file_url = $resources_dir . $file_name;
    $pos = stripos($file_name, '_');
    $table_name = substr($file_name, 0, $pos);

    if (($handle = fopen("$file_url", "r")) !== FALSE) {

        $headers = fgetcsv($handle, 1000, ",");
        $image_columns = $_POST;

        foreach ($image_columns as $column) {
            $key = array_search($column, $headers);
            if ($key !== FALSE) {
                $image_indexes[] = $column;
            }
        }

        $all_headers = getResourceFromXML($GLOBALS['res_file'], $table_name . '_all_headers');

        $variant_headers = getResourceFromXML($GLOBALS['res_file'], $table_name . '_variants_headers');

        $group_headers = getResourceFromXML($GLOBALS['res_file'], $table_name . '_groups_headers');

        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {

            $data_size = count($data);
            if ($data_size < 34) {
                $result = array_pad($data, 34, "");
                $data = $result;
            }
            if ($data_size > 34) {
                $result = array_slice($data, 0, 34);
                $data = $result;
            }
            $keyed_data = array_combine($all_headers, $data);

            // still have problem at product id 104. Next line is 
            if ($keyed_data['Product_ID'] == 0) {
                return FALSE;
            }

            $new_data = process_variants($keyed_data, $image_indexes, $variant_headers);
            $concat = FALSE;

            insertRow($conn, $new_data, $table_name . '_variants', $concat);

            $new_data = process_groups($keyed_data, $group_headers);
            $concat = TRUE;
            insertRow($conn, $new_data, $table_name . '_groups', $concat);
        }
        fclose($handle);
    }
}

function process_variants($data, $image_indexes, $variant_headers) {

    foreach ($image_indexes as $index) {
        if (isset($data[$index])) {
            $image_array = explode(',', $data[$index]);
            $compare_url = $image_array[0];

            $image_name = getImageFromWeb($compare_url);
            $data[$index] = $image_name;

            foreach ($image_array as $image_url) {
                if (strcasecmp($compare_url, $image_url) != 0) {
                    /** Do something to allow multiple images inside one field
                      $image_name = getImageFromWeb($image_url);
                      $data[$index] = $image_name; */
                }
            }
        }
    }

    $new_data['Selling'] = FALSE;

    if ($data['Product_ID'] == 0) {
        alert('zero');
    }

    foreach ($variant_headers as $header) {
        $new_data[$header] = $data[$header];
    }

    return $new_data;
}

function process_groups($data, $group_headers) {

    $data ['Base_SKU'] = getBaseSKU($data['SKU']);
    $data['Variant_IDs'] = $data['Product_ID'];
    $data['Name'] = getBaseName($data['Name']);

    foreach ($group_headers as $header) {
        $new_data[$header] = $data[$header];
    }

    return $new_data;
}

function getImageFromWeb($file_url) {

    $media_dir = $GLOBALS['media_dir'];

    if (!is_dir($media_dir)) {
        mkdir($media_dir);
    }

    $image_path = $media_dir . basename($file_url);

    if (!file_exists($image_path)) {
        copy($file_url, $image_path);
    }
    return $image_path;
}

function getBaseName($name) {
    
    $resource_file = $GLOBALS['res_file'];
    $colours = getResourceFromXML($resource_file, 'alterego_colours');

    foreach ($colours as $colour) {
        $index = stripos($name, $colour);
        if ($index !== FALSE) {
            $base_name = substr($name, 0, $index);         
            return $base_name;
        }
    }
}

function splitSKU($sku) {

    $new_sku = str_ireplace('/', '', $sku);

    $resource_file = $GLOBALS['res_file'];
    $colours = getResourceFromXML($resource_file, 'alterego_colours');

    foreach ($colours as $colour) {
        $index = stripos($new_sku, $colour);
        if ($index !== FALSE) {
            $split_sku[] = substr($new_sku, 0, $index);
            $split_sku[] = $colour;
            $split_sku[] = substr($new_sku, $index + strlen($colour));
            return $split_sku;
        }
    }

    $sizes = getResourceFromXML($resource_file, 'alterego_sizes');


    foreach ($sizes as $size) {
        $index = stripos($new_sku, $size);
        if ($index !== FALSE) {
            $split_sku[] = substr($new_sku, 0, $index);
            $split_sku[] = 'no colour';
            $split_sku[] = $size;
            return $split_sku;
        }
    }
    return FALSE;
}

function getBaseSKU($sku) {

    $resource_file = $GLOBALS['res_file'];
    $colours = getResourceFromXML($resource_file, 'alterego_colours');

    foreach ($colours as $colour) {
        $index = stripos($sku, $colour);
        if ($index !== FALSE) {
            return substr($sku, 0, $index);
        }
    }

    $sizes = getResourceFromXML($resource_file, 'alterego_sizes');

    foreach ($sizes as $size) {
        $index = stripos($sku, $size);
        if ($index !== FALSE) {
            return substr($sku, 0, $index);
        }
    }
    //try with first 8 characters to build sku
    return substr($sku, 0, 8);
}

function updateDB() {

    session_start();
    $file_name = $_SESSION["filename"];
    $pos = stripos($file_name, '_');
    $table_name = substr($file_name, 0, $pos);
//    $primary_key = 'Product_ID';
//    $added_columns ['Selling'] = 'BOOLEAN';

    $conn = openDB('rwk_productchooserdb');
    // create product variants table
//    createTable($conn, $table_name . '_variants', $added_columns, $primary_key);
    $fields_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_variants_headers', 'type');
    $fields_array = array('Selling' => 'BOOLEAN') + $fields_array;
    $indices_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_indices', 'index');
    createTable($conn, $table_name . '_variants', $fields_array, $indices_array);
    // create product groups table
    $primary_key = 'Base_SKU';
    $fields_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_groups_headers', 'type');
    unset($indices_array);
    createTable($conn, $table_name . '_groups', $fields_array, $indices_array);

    populateTableFromCSV($conn, $file_name);
}

function showProducts($start_row, $items_per_page) {

    if ($start_row < 0) {
        $start_row = 0;
    }
    $pos = stripos($_POST['table_name'], '_');
    $table_name = substr($_POST['table_name'], 0, $pos);

    $conn = openDB('rwk_productchooserdb');

    $data = getProductData($conn, $table_name, $start_row, $items_per_page);
    $html = product_data_to_html2($data);

    return implode(' ', $html);
}

function product_data_to_html2($data) {

    $html_array[] = '<div id=\'product_data\' class="base-layer">';

    foreach ($data as $array) {
        $keys = array_keys($array);
        $count = count($array);
        $first_variant = $array[$keys[0]];
        $first_id = $array[$keys[0]]['Product_ID'];

        $html_array[] = '<div class="product_box">';

        $html_array[] = '<div class="left-box">';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<div id="carousel_' . $first_id . '" class="carousel">';
        $html_array[] = '<input id="left_btn_' . $first_id . '" type="button" value="<" class="left-button image-slide-btn" name="left_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="left"/>';
        $html_array[] = '<input id="right_btn_' . $first_id . '" type="button" value=">" class="right-button image-slide-btn" name="right_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="right"/>';

        $html_array[] = '<ul data-count="' . $count . '">';

        if ($count === 0) {
            //use 'image coming soon placeholder
            $no_image = './image_coming_soon.jpg';
            $html_array[] = '<li><img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;"></li>';
        } else {
            foreach ($array as $variant) {
                $html_array[] = '<li><img id="image_' . $variant['Product_ID'] . '" class="image" src="' . $variant['Image'] . '"></li>';
            }
        }
        $html_array[] = '</ul>';

        $html_array[] = '</div>'; // carousel
        $html_array[] = '</div>'; //table-row
        $html_array[] = '</div>'; // left-box

        $html_array[] = '<div class="right-box">';

        $html_array[] = '<div class="table-row">';

        foreach ($array as $variant) {
            $html_array[] = '<div class="info-box">';

            $html_array[] = '<div class="table-row">';
            $html_array[] = '<span class="left-span">Name : <label id="name_' . $variant['Product_ID'] . '">' . $variant['Name'] . '</label></span>';
            $html_array[] = '</div>'; // table-row
            $html_array[] = '<div class="table-row">';
            $html_array[] = '<span class="left-span">Price : £<label id="price_' . $variant['Product_ID'] . '">' . $variant['Price_RRP'] . '</label></span>';
            $html_array[] = '<span class="left-span">Trade Price : £<label id="trade_price_' . $variant['Product_ID'] . '">' . $variant['Trade_Price'] . '</label></span>';

            if ($array[$keys[0]]['Selling'] == TRUE) {
                $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $variant['Product_ID'] . '" class="selling_checkbox" type="checkbox"  data-id="' . $variant['Product_ID'] . '" checked></span>';
            } else {
                $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $variant['Product_ID'] . '" class="selling_checkbox"  type="checkbox"  data-id="' . $variant['Product_ID'] . '"></span>';
            }
            $html_array[] = '</div>'; // table-row
            $html_array[] = '<div class="table-row">';
            $html_array[] = '<span class="left-span">Product ID : <label id="product_id_' . $variant['Product_ID'] . '">' . $variant['Product_ID'] . '</label></span>';
            $html_array[] = '<span class="left-span">SKU : <label id="sku_' . $variant['Product_ID'] . '">' . $variant['SKU'] . '</label></span>';
            $html_array[] = '</div>'; // table-row

            $html_array[] = '<div class="table-row">';
            $html_array[] = '<span class="left-span">Variations</span>';
            $html_array[] = '<span class="left-span">Size : <label id="size_' . $variant['Product_ID'] . '">' . $variant['Size'] . '</label></span>';
            $html_array[] = '<span class="left-span">Colour : <label id="colour_' . $variant['Product_ID'] . '">' . $variant['Colour'] . '</label></span>';
            $html_array[] = '</div>'; // table-row
            $html_array[] = '</div>'; //info-box
        }
        $html_array[] = '</div>'; // table-row

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<p class="left-span"><label id="description_' . $first_id . '">' . $first_variant['Description'] . '</label></p>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '</div>'; // right-box
        $html_array[] = '</div>'; // product-box
        /*
          $pos = stripos($array['Image'], '.jpg');
          if ($pos !== FALSE){
          $array[Image] = substr_replace($array[Image], '-150x150', $pos, 0);
          } */
    }
    $html_array[] = '</div>'; // base-layer

    return $html_array;
}

function product_data_to_html($data) {

    $html_array[] = '<div id=\'product_data\' class="base-layer">';

    foreach ($data as $array) {
        $keys = array_keys($array);
        $count = count($array);
        $first_variant = $array[$keys[0]];
        $first_id = $array[$keys[0]]['Product_ID'];

        $html_array[] = '<div class="product_box">';

        $html_array[] = '<div class="left-box">';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<div id="carousel_' . $first_id . '" class="carousel">';
        $html_array[] = '<input id="left_btn_' . $first_id . '" type="button" value="<" class="left-button image-slide-btn" name="left_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="left"/>';
        $html_array[] = '<input id="right_btn_' . $first_id . '" type="button" value=">" class="right-button image-slide-btn" name="right_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="right"/>';

        $html_array[] = '<ul data-count="' . $count . '">';

        if ($count === 0) {
            //use 'image coming soon placeholder
            $no_image = './image_coming_soon.jpg';
            $html_array[] = '<li><img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;"></li>';
        } else {
            foreach ($array as $variant) {
                $html_array[] = '<li><img id="image_' . $variant['Product_ID'] . '" class="image" src="' . $variant['Image'] . '"></li>';
            }
        }
        $html_array[] = '</ul>';

        $html_array[] = '</div>'; // carousel
        $html_array[] = '</div>'; //table-row
        $html_array[] = '</div>'; // left-box

        $html_array[] = '<div class="right-box">';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Name : <label id="name_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Name'] . '</label></span>';
        $html_array[] = '<span class="left-span">Price : £<label id="price_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Price_RRP'] . '</label></span>';
        $html_array[] = '<span class="left-span">Trade Price : £<label id="trade_price_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Trade_Price'] . '</label></span>';
        $html_array[] = '</div>';

        $html_array[] = '<div class="table-row">';
        if ($array[$keys[0]]['Selling'] === TRUE) {
            $html_array[] = '<span class="left-span">Selling : <input id="checkbox_' . $array[$keys[0]]['Product_ID'] . '" type="checkbox" checked></span>';
        } else {
            $html_array[] = '<span class="left-span">Selling : <input id="checkbox_' . $array[$keys[0]]['Product_ID'] . '" type="checkbox" ></span>';
        }
        $html_array[] = '<span class="left-span">Product ID : <label id="product_id_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Product_ID'] . '</label></span>';
        $html_array[] = '<span class="left-span">SKU : <label id="sku_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['SKU'] . '</label></span>';
        $html_array[] = '</div>';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Variations</span>';
        $html_array[] = '<span class="left-span">Size : <label id="size_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Size'] . '</label></span>';
        $html_array[] = '<span class="left-span">Colour : <label id="colour_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Colour'] . '</label></span>';
        $html_array[] = '</div>';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<p class="left-span"><label id="description_' . $array[$keys[0]]['Product_ID'] . '">' . $array[$keys[0]]['Description'] . '</label></p>';
        $html_array[] = '</div>';

        $html_array[] = '</div>'; // right-box
        $html_array[] = '</div>'; // product-box
        /*
          $pos = stripos($array['Image'], '.jpg');
          if ($pos !== FALSE){
          $array[Image] = substr_replace($array[Image], '-150x150', $pos, 0);
          } */
    }
    $html_array[] = '</div>'; // base-layer

    return $html_array;
}

function updateSelling() {

    $table_name = $_POST['table_name'];
    $selling_list = $_POST['selling'];

    $conn = openDB('rwk_productchooserdb');
    updateSellingDB($conn, $table_name, $selling_list);
}

function exportToCSV() {

    // get max number of rows in variants table
    // get each row from groups table
    // create variable product for that group
    // get each variation from group
    // if selling = true format row and put it in csv file
    // go to next group
    // download csv to local pc

    $ini_val = ini_get('upload_tmp_dir');
    $temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
    $table_name = $_POST['table_name'];
    $pos = stripos($table_name, '_');
    $groups_table = substr($table_name, 0, $pos) . '_groups';

    $conn = openDB('rwk_productchooserdb');
    $file_url = $temp_path . '/' . $table_name . '.csv';

    $row = 0;
    $max_id = get_num_rows($table_name);

    if (($handle = fopen("$file_url", "w")) !== FALSE) {

        $headers = getResourceFromXML($GLOBALS['res_file'], 'woo_headers');
        $result = fputcsv($handle, $headers);
        while (($result = getRow($conn, $groups_table, $row)) !== FALSE) {

            create_variable_product($result);

            $csv_line = process_row_for_export($result, $max_id);
            fputcsv($handle, $csv_line);
            $row ++;
        }
    }

    fclose($handle);
    return TRUE;
}

function create_variable_product($data) {
    
}

function get_num_rows($table_name) {

    $rowSQL = mysql_query("SELECT MAX( Product_ID ) AS max FROM $table_name;");
    $row = mysql_fetch_array($rowSQL);
    $largestNumber = $row['max'];
    return $largestNumber;
}

function process_row_for_export($data, $max_id) {

    $max_id = $max_id * 10;
    $variable_array = [];
}
