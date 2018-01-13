<?php

function getFileList() { // ***USING***
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

/*
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
 */
function useCSV() { // ***USING***
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

function useCSV_OLD() { // ***USING***
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

function uploadCSV() {// ***USING ***
//    $target_path = $GLOBALS['res_dir'];
//    $file_name = basename($_FILES['uploadedfile']['name']);
    $file_name = 'alterego_current.csv';
    $source_file = "D:/Documents/work/Seduce/" . $file_name;
    
    $file_name = str_replace('-', '_', $file_name);
    
    $target_path = $_SERVER['DOCUMENT_ROOT'] . '/ImagesFromCSV/resources/' . $file_name;

    if (copy($source_file, $target_path) !== TRUE) {
        return FALSE;
    }

//    if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
////        $headers = getCSVHeaders($file_name);
////        $str = headersToHtml($headers);
    
    session_start();
    $_SESSION["filename"] = $file_name;
////        return $str;
    return $file_name;
//    } else {
//        exit("There was an error uploading the file, please try again!");
//    }
}

function getCSVHeaders($file_name) { // ***USING***
    $resources_dir = $GLOBALS['res_dir'];
    $file_url = $resources_dir . $file_name;

    if (($handle = fopen("$file_url", "r")) !== FALSE) {

        $headers = fgetcsv($handle, 1000, ",");
        fclose($handle);
    }
    return $headers;
}

function headersToHtml($headers) { // ***USING***
    $html_array[] = '<div class="checkbox_container">';
    foreach ($headers as $header) {
        $html_array[] = '<div class="input_class"><input type="checkbox" class="input_label" id="' . $header . '" name="' . (string) $header . '" value="' . (string) $header . '"><label for="' . $header . '">' . (string) $header . '</div>';
    }
    $html_array[] = '</div>';
    return implode(" ", $html_array);
}

function get_group_id_base($largest_id) {

    $len = strlen($largest_id);
    $base = pow(10, $len);

    return $base;
}

function getImageFromWeb($file_url) { // ***USING***
    $media_dir = $GLOBALS['media_dir'];

    if (!is_dir($media_dir)) {
        mkdir($media_dir);
    }

    $image_path = $media_dir . basename($file_url);
//    $source = explode(',', $file_url);
//
//    if (!file_exists($image_path)) {
//        copy($source[0], $image_path);
//    }
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

/*
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
 */
function getBaseSKU($sku) { // ***USING***
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

/*
  function spaces_to_underscore($array, $change_keys = FALSE) {

  if ($change_keys) {
  foreach ($array as $key => $value) {
  $key = str_replace('?', '', $key);
  $key = str_replace('(', '', $key);
  $key = str_replace(')', '', $key);
  $key = str_replace('-', '_', $key);
  $new_array[str_replace(' ', '_', $key)] = $value;
  }
  } else {
  foreach ($array as $key => $value) {
  $value = str_replace('?', '', $value);
  $value = str_replace('(', '', $value);
  $value = str_replace(')', '', $value);
  $key = str_replace('-', '_', $key);
  $array[$key] = str_replace(' ', '_', $value);
  }
  }
  return $new_array;
  }
 */
function showProducts($start_row, $items_per_page, $filters = FALSE) { // ***USING***
    if ($start_row < 0) {
        $start_row = 0;
    }
    if (!isset($_SESSION)) {
        session_start();
    }
    $table_name = $_SESSION['table_name'];

    $conn = openDB('rwk_productchooserdb');

    $data = getProductData($conn, $table_name, $start_row, $items_per_page, $filters);
    $html = product_data_to_html($data);

    return implode(' ', $html);
}

function product_data_to_html($data) { // ***USING***
    $html_array[] = '<div id="product_data" class="base-layer">';

    foreach ($data as $product) {

        $html_array[] = '<div class="product_box">';

        $html_array[] = '<div class="left-box">';
        $html_array[] = '<div class="table-row">';
        $html_array[] = '<div id="image_box_' . $product['Product_ID'] . '" class="image_box">';
        if ($product['Image'] === "") {
            //use 'image coming soon placeholder
            $no_image = './image_coming_soon.jpg';
//            $html_array[] = '<li><img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;"></li>';
            $html_array[] = '<img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;">';
        } else {
//            $html_array[] = '<li><img id="image_' . $product['Product_ID'] . '" class="image" src="' . $product['Image'] . '"></li>';
            $html_array[] = '<img id="image_' . $product['Product_ID'] . '" class="image" src="' . $product['Image'] . '">';
        }
        $html_array[] = '</div>'; // image_box_
        $html_array[] = '</div>'; //table-row
        $html_array[] = '</div>'; // left-box

        $html_array[] = '<div class="right-box">';
        
        $html_array[] = '<div class="table-row">';
        $html_array[] = '<div class="info-box">';

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Name : <label id="name_' . $product['Product_ID'] . '">' . $product['Name'] . '</label></span>';
        $html_array[] = '<span class="left-span">Brand : <label id="brand_' . $product['Product_ID'] . '">' . $product['Brand'] . '</label></span>';
        $html_array[] = '</div>'; // table-row
        
        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Price : £<label id="price_' . $product['Product_ID'] . '">' . $product['Price_RRP'] . '</label></span>';
        $html_array[] = '<span class="left-span">Trade Price : £<label id="trade_price_' . $product['Product_ID'] . '">' . $product['Trade_Price'] . '</label></span>';

        if ($product['Selling'] == TRUE) {
            $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $product['Product_ID'] . '" class="selling_checkbox" type="checkbox"  data-id="' . $product['Product_ID'] . '" checked></span>';
        } else {
            $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $product['Product_ID'] . '" class="selling_checkbox"  type="checkbox"  data-id="' . $product['Product_ID'] . '"></span>';
        }
        $html_array[] = '</div>'; // table-row
        
        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Product ID : <label id="product_id_' . $product['Product_ID'] . '">' . $product['Product_ID'] . '</label></span>';
        $html_array[] = '<span class="left-span">SKU : <label id="sku_' . $product['Product_ID'] . '">' . $product['SKU'] . '</label></span>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Variations</span>';
        $html_array[] = '<span class="left-span">Size : <label id="size_' . $product['Product_ID'] . '">' . $product['Size'] . '</label></span>';
        $html_array[] = '<span class="left-span">Colour : <label id="colour_' . $product['Product_ID'] . '">' . $product['Colour'] . '</label></span>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Stock Type : <label id="stock_type_' . $product['Product_ID'] . '">' . $product['Stock_Type'] . '</label></span>';
        $html_array[] = '<span class="left-span">Stock Level : <label id="stock_level_' . $product['Product_ID'] . '">' . $product['Stock_Level'] . '</label></span>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '</div>'; //info-box
        $html_array[] = '</div>'; // table-row

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<p class="left-span"><label id="description_' . $product['Product_ID'] . '">' . $product['Description'] . '</label></p>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '</div>'; // right-box
        $html_array[] = '</div>'; // product-box
    }
    $html_array[] = '</div>'; // base-layer

    return $html_array;
}

function updateSelling() { // ***USING***
    if (!isset($_SESSION)) {
        session_start();
    }
    $table_name = $_SESSION['table_name'];
    $selling_list = $_POST['selling'];

    $conn = openDB('rwk_productchooserdb');
    updateSellingDB($conn, $table_name, $selling_list);
}

function exportToCSV($wholesaler) {

    $ini_val = ini_get('upload_tmp_dir');
    $temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
    if (!isset($_SESSION)) {
        session_start();
    }
    $table_name = $_SESSION['table_name'];

    $conn = openDB('rwk_productchooserdb');
    $file_url = $temp_path . '/' . $table_name . '.csv';

    $row = 0;
    $max_id = get_largest_id($table_name);
    $group_id_base = get_group_id_base($max_id);

    if (($handle = fopen("$file_url", "w")) !== FALSE) {

        $woo_headers = array_keys(getResourceFromXML($GLOBALS['res_file'], $wholesaler . '_map', 'map'));

        $result = fputcsv($handle, $woo_headers);

        $groups = getGroups($conn, $table_name);
        foreach ($groups as $group) {
            $result = getProductsBySKU($group, $table_name);
            $groupArray = createGroup($result, $wholesaler, $group);
            foreach ($groupArray as $product) {
                fputcsv($handle, $product);
            }
        }
    }

    fclose($handle);
    return TRUE;
}

function createGroup($result, $wholesaler, $group) {

    $map = getResourceFromXML($GLOBALS['res_file'], $wholesaler . '_map', "map", TRUE);
    $group_added = FALSE;
    $num_products = count($result);

    foreach ($map as $wookey => $woovalue) {

        if (stripos($wookey, 'attribute') === FALSE) {
            $new_array[0][$wookey] = $woovalue === "" ? "" : $result[0][$woovalue];
            for ($i = 1; $i <= $num_products; $i++) {
                $new_array[$i][$wookey] = $woovalue === "" ? "" : $result[$i - 1][$woovalue];
            }
        } else {
            if ($woovalue !== "") {
                $num = intval(preg_replace('/[^0-9]+/', '', $wookey), 10);
                $new_array[0][$wookey] = $woovalue;
                for ($i = 1; $i <= $num_products; $i++) {
                    $new_array[$i][$wookey] = $woovalue;
                }
                for ($i = 1; $i <= $num_products; $i++) {
                    $new_array[0]['Attribute ' . $num . ' value(s)'] = $new_array[0]['Attribute ' . $num . ' value(s)'] . ',' . $result[$i - 1][$woovalue];
                    $new_array[$i]['Attribute ' . $num . ' value(s)'] = $result[$i - 1][$woovalue];

                    $new_array[$i]['Attribute ' . $num . ' visible'] = 1;
                    $new_array[$i]['Attribute ' . $num . ' global'] = 1;
                    $new_array[$i]['Attribute ' . $num . ' default'] = "";
                }
                $new_array[0]['Attribute ' . $num . ' value(s)'] = ltrim($new_array[0]['Attribute ' . $num . ' value(s)'], ',');
                $new_array[0]['Attribute ' . $num . ' visible'] = 1;
                $new_array[0]['Attribute ' . $num . ' global'] = 1;
                $new_array[0]['Attribute ' . $num . ' default'] = $result[0][$woovalue];
            }
        }
    }
    $new_array[0]['Name'] = getBaseName($new_array[0]['Name']);
    $new_array[0]['Type'] = 'variable';
    $new_array[0]['Published'] = '1';
    $new_array[0]['Is featured?'] = '0';
    $new_array[0]['Visibility in catalogue'] = 'visible';
    $new_array[0]['Backorders allowed?'] = '0';
    $new_array[0]['Sold individually?'] = '0';
    $new_array[0]['Allow customer reviews?'] = '0';
    $new_array[0]['Position'] = '0';
    $new_array[0]['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($result[0]['Image'], '.');
    $new_array[0]['SKU'] = $group['Base_SKU'];

    for ($i = 1; $i <= $num_products; $i++) {
        $new_array[$i]['Type'] = 'variation';
        $new_array[$i]['Published'] = '1';
        $new_array[$i]['Is featured?'] = '0';
        $new_array[$i]['Visibility in catalogue'] = 'visible';
        $new_array[$i]['Backorders allowed?'] = '0';
        $new_array[$i]['Sold individually?'] = '0';
        $new_array[$i]['Allow customer reviews?'] = '0';
        $new_array[$i]['Position'] = '0';
        $new_array[$i]['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($result[$i - 1]['Image'], '.');
        $new_array[$i]['SKU'] = str_replace('/', '', $new_array[$i]['SKU']);
        $new_array[$i]['Parent'] = $group['Base_SKU'];
    }

    return $new_array;
}

function generateFilters() { // ***USING***
    $html_array[] = '<span class="left-span">All : <input id="all" name="all" value="All" class="filter_type" type="checkbox" checked></span>';
    $html_array[] = '<span class="left-span">Stock Green : <input name="stock_green" value="Stock_Level=green" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Stock Amber : <input name="stock_amber" value="Stock_Level=amber" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Stock Red : <input name="stock_red" value="Stock_Level=red" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Discontinued : <input name="stock_discontinued" value="Stock_Type =Discontinued" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Pre Order : <input name="stock_pre_order" value="Stock_Type=Pre-Order Continuity" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Stock Line : <input name="stock_line" value="Stock_Type=Stock Line" class="filter_type" type="checkbox" unchecked></span>';
    $html_array[] = '<span class="left-span">Brand Bassaya : <input name="brand" value="Brand=Bassaya" class="filter_type" type="checkbox" unchecked></span>';

    echo implode(' ', $html_array);
}
