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

function uploadCSV() {//$file_name) {
//    if (empty($file_name)) {
//        return FALSE;
//    }
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

function populateTableFromCSV($conn, $file_name, $create_groups = TRUE) {

    $resources_dir = $GLOBALS['res_dir'];
    $file_url = $resources_dir . $file_name;
    $pos = stripos($file_name, '_');
    $table_name = substr($file_name, 0, $pos);

    bulkFillTable($conn, $file_name);
    createGroupsTable($conn, $file_name);
    populateGroupsTable($conn, $file_name);
}

function populateTableFromCSV_old($conn, $file_name, $create_groups = TRUE) {

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

        $group_array = [];

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

//            $new_data = process_variants($keyed_data, $image_indexes, $variant_headers, $table_name);
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

            $baseSKU = getBaseSKU($keyed_data['SKU']);
            $group_id = array_search($baseSKU, $group_array);

            if ($group_id === FALSE) {
                $group_array[] = $baseSKU;
                $group_id = count($group_array);
            } else {
                $group_id += 1;
            }

            $variant_data['Parent'] = $group_id;
            $variant_data['Selling'] = FALSE;

            foreach ($variant_headers as $header) {
                $variant_data[$header] = $keyed_data[$header];
            }

            $concat = FALSE;
            insertRow($conn, $variant_data, $table_name . '_variants', $concat);
        }
        fclose($handle);
        mysql_insert_array($table_name . '_groups', $group_array);
    }
}

function process_variants($data, $image_indexes, $variant_headers, $table_name) {

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

    $baseSKU = getBaseSKU($data['SKU']);
    $group_id = skuExists($baseSKU, $table_name . '_groups');

    if ($group_id) {
        $new_data['Parent'] = $group_id;
    } else {
        $new_data = process_groups($keyed_data, $image_indexes, $group_headers);
        $concat = TRUE;
        insertRow($conn, $new_data, $table_name . '_groups', $concat);
        $new_data['Parent'] = 0;
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

function process_groups($data, $image_indexes, $group_headers) {

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

    $data ['SKU'] = getBaseSKU($data['SKU']);
    $data['Name'] = getBaseName($data['Name']);

    foreach ($group_headers as $header) {
        $new_data[$header] = $data[$header];
    }

    $new_data['Variant_IDs'] = $data['Product_ID'];

    return $new_data;
}

function get_group_id_base($largest_id) {

    $len = strlen($largest_id);
    $base = pow(10, $len);

    return $base;
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
//    $fields_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_variants_headers', 'type');
//    $fields_array = array('Selling' => 'BOOLEAN') + $fields_array;
//    $fields_array = array('Parent' => 'INT(10) UNSIGNED') + $fields_array;
//    $indices_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_indices', 'index');
//    createTable($conn, $table_name . '_variants', $fields_array, $indices_array);
//    // create product groups table
//    $fields_array = getResourceFromXML($GLOBALS['res_file'], $table_name . '_groups_headers', 'type');
//    unset($indices_array);
//    createTable($conn, $table_name . '_groups', $fields_array, $indices_array);

    populateTableFromCSV($conn, $file_name);

//    $fields_array = getResourceFromXML($GLOBALS['res_file'], 'woo_headers', 'type');
//    $fields_array = spaces_to_underscore($fields_array, TRUE);
//    $fields_array = array('MapFrom' => 'VARCHAR(255)') + $fields_array;
//
//    createTable($conn, 'woo_map', $fields_array, $indices_array);
}

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

function showProducts($start_group_id, $items_per_page, $filter = FALSE) {

    // for $start_row read group_id
    if ($start_group_id < 0) {
        $start_group_id = 0;
    }
//    $pos = stripos($_POST['table_name'], '_');
    $table_name = $_POST['table_name']; //substr($_POST['table_name'], 0, $pos);

    $conn = openDB('rwk_productchooserdb');

    $data = getProductData($conn, $table_name, $start_group_id, $items_per_page, $filter);
    $html = product_data_to_html($data);

    return implode(' ', $html);
}

function product_data_to_html($data) {

    $html_array[] = '<div id=\'product_data\' class="base-layer">';

    foreach ($data as $product) {
        $keys = array_keys($product);
        $count = count($product);

        $html_array[] = '<div class="product_box">';

        $html_array[] = '<div class="left-box">';
        $html_array[] = '<div class="table-row">';

        if ($count === 0) {
            //use 'image coming soon placeholder
            $no_image = './image_coming_soon.jpg';
            $html_array[] = '<li><img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;"></li>';
        } else {
            $html_array[] = '<li><img id="image_' . $product['Product_ID'] . '" class="image" src="' . $product['Image'] . '"></li>';
        }
        
        $html_array[] = '</div>'; //table-row
        $html_array[] = '</div>'; // left-box

        $html_array[] = '<div class="right-box">';
        $html_array[] = '<div class="table-row">';

        $html_array[] = '<div class="info-box">';
        $html_array[] = '<div class="table-row">';
        $html_array[] = '<span class="left-span">Name : <label id="name_' . $product['Product_ID'] . '">' . $product['Name'] . '</label></span>';
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
        $html_array[] = '</div>'; //info-box

        $html_array[] = '</div>'; // table-row

        $html_array[] = '<div class="table-row">';
        $html_array[] = '<p class="left-span"><label id="description_' . $first_id . '">' . $first_variant['Description'] . '</label></p>';
        $html_array[] = '</div>'; // table-row

        $html_array[] = '</div>'; // right-box
        $html_array[] = '</div>'; // product-box
    }
    $html_array[] = '</div>'; // base-layer

    return $html_array;
}

function product_data_to_html_OLD2($data) {

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

function product_data_to_html_OLD($data) {

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

function updateSelling() {

    $table_name = $_POST['table_name'];
    $selling_list = $_POST['selling'];

    $conn = openDB('rwk_productchooserdb');
    updateSellingDB($conn, $table_name, $selling_list);
}

function exportToCSV() {

    $ini_val = ini_get('upload_tmp_dir');
    $temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
    $variants_table = $_POST['table_name'];
    $pos = stripos($variants_table, '_');
    $table_name = substr($variants_table, 0, $pos);

    $conn = openDB('rwk_productchooserdb');
    $file_url = $temp_path . '/' . $variants_table . '.csv';

    $row = 0;
    $max_id = get_largest_id($variants_table);
    $group_id_base = get_group_id_base($max_id);

    if (($handle = fopen("$file_url", "w")) !== FALSE) {

        $woo_headers = array_keys(getResourceFromXML($GLOBALS['res_file'], $table_name . '_map', 'map'));

        $result = fputcsv($handle, $woo_headers);
        while (($group = getRow($conn, $table_name . '_groups', $row)) !== FALSE) {

            $variations = getProductByID($table_name . '_variants', $group['Variant_IDs']);
            $variable_products = create_variable_product($group, $variations, $table_name);
            foreach ($variable_products as $product) {
                fputcsv($handle, $product);
            }
            $row ++;
        }
    }

    fclose($handle);
    return TRUE;
}

function create_variable_product($group, $variations, $table_name) {

    $map = getResourceFromXML($GLOBALS['res_file'], $table_name . '_map', "map", TRUE);

    foreach ($map as $wookey => $woovalue) {

        if (stripos($wookey, 'attribute') !== FALSE) {
            if ($woovalue !== 'Skip') {
                $num = intval(preg_replace('/[^0-9]+/', '', $wookey), 10);
                $new_array[$wookey] = $woovalue;
                if (isset($new_array['Attribute ' . $num . ' value(s)'])) {
                    $new_array['Attribute ' . $num . ' value(s)'] = $new_array['Attribute ' . $num . ' value(s)'] . ', ' . $group[$woovalue];
                } else {
                    $new_array['Attribute ' . $num . ' value(s)'] = $group[$woovalue];
                }
                $new_array['Attribute ' . $num . ' visible'] = 1;
                $new_array['Attribute ' . $num . ' global'] = 1;
                $new_array['Attribute ' . $num . ' default'] = $group[$woovalue];
            }
        } else {
            $new_array[$wookey] = $woovalue == "" ? "" : $group[$woovalue];
        }
    }
    $new_array['Type'] = 'variable';
    $new_array['Published'] = '1';
    $new_array['Is featured?'] = '0';
    $new_array['Visibility in catalogue'] = 'visible';
    $new_array['Backorders allowed?'] = '0';
    $new_array['Sold individually?'] = '0';
    $new_array['Allow customer reviews?'] = '0';
    $new_array['Position'] = '0';
    $new_array['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($group['Image'], '.');

    $results[] = $new_array;

    $variations_count = 0;

    foreach ($variations as $variation) {
        if ($variation['Selling'] === '1') {
            $variations_count++;
            foreach ($map as $wookey => $woovalue) {
                if ($wookey === 'SKU') {
                    $variation[$woovalue] = str_replace('/', '', $variation[$woovalue]);
                }
                if (stripos($wookey, 'attribute') !== FALSE) {
                    if ($woovalue !== 'Skip') {
                        $num = intval(preg_replace('/[^0-9]+/', '', $wookey), 10);
                        $new_array[$wookey] = $woovalue;
                        $key = 'Attribute ' . $num . ' value(s)';
                        $new_array[$key] = $variation[$woovalue];
                        $new_array['Attribute ' . $num . ' visible'] = 1;
                        $key = 'Attribute ' . $num . ' global';
                        $new_array[$key] = 1;
                        $key = 'Attribute ' . $num . ' default';
                        $new_array[$key] = $variation[$woovalue];
                    }
                } else {
                    $new_array[$wookey] = $woovalue == "" ? "" : $variation[$woovalue];
                }
            }
            $new_array['Type'] = 'variation';
            $new_array['Published'] = '1';
            $new_array['Is featured?'] = '0';
            $new_array['Visibility in catalogue'] = 'visible';
            $new_array['Backorders allowed?'] = '0';
            $new_array['Sold individually?'] = '0';
            $new_array['Allow customer reviews?'] = '0';
            $new_array['Position'] = '0';
            $new_array['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($variation['Image'], '.');
            $new_array['Parent'] = $group['SKU'];

            $results[] = $new_array;
        }
    }

    if ($variations_count > 0) {
        return $results;
    } else {
        return null;
    }
}

function generateFilters() {

    $html_array[] = '<select id="filter_type"  name="filter_type">';
    $html_array[] = ' <option name="all"value="All" selected>All</option>';
    $html_array[] = ' <option name="stock_green" value="Stock_Level = green">Stock Level Green</option>';
    $html_array[] = '<option name="stock_amber" value="Stock_Level = amber">Stock Level Amber</option>';
    $html_array[] = '<option name="stock_red" value="Stock_Level = red">Stock Level Red</option>';
    $html_array[] = '<option name="stock_discontinued" value="Stock_Type = Discontinued">Stock Type Discontinued</option>';
    $html_array[] = '<option name="stock_pre_order" value="Stock_Type = Pre-Order Continuity">Stock Type Pre Order</option>';
    $html_array[] = '<option name="stock_line" value="Stock_Type = Stock Line">Stock Type Stock Line</option>';
    $html_array[] = '<option name="brand" value="Brand = Bassaya">Bassaya</option>';
    $html_array[] = '<option name="brand" value="Brand = Avanua">Avanua</option>';
    $html_array[] = '<option name="brand" value="Brand = Roza">Roza</option>';
    $html_array[] = '</select>';

    echo implode(' ', $html_array);
}
