<?php

function connectToServer() {

    $servername = "localhost";
    $username = "root";
    $password = "";

    //create connection
    $conn = new mysqli($servername, $username, $password);
    //check conneciton
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function connectToDb($db_name) {

    $servername = "localhost";
    $username = "root";
    $password = "";

    //create connection
    $conn = new mysqli($servername, $username, $password, $db_name);
    //check conneciton
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function closeConnection($conn) {
    $conn->close();
}

function openDB($db_name) {

    // connect to server
    $conn = connectToServer();

    $sql = "CREATE DATABASE IF NOT EXISTS  {$db_name}";
    if ($conn->query($sql) !== TRUE) {
        die("Error creating database: " . $conn->error);
    }

    // connect to database
    $conn = connectToDb($db_name);

    return $conn;
}

function createTable($conn, $table_name, $fields_array, $indices_array) {
    $result = $conn->query("SHOW TABLES LIKE " . $table_name);

    if ($result === FALSE) {
        // create the table
//        $file_path = $GLOBALS['res_file'];
//        $resource = $table_name . '_headers';
//        $headers = getResourceFromXML($file_path, $resource);
//        $headers = getColumnsArray($table_name);

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (";

        foreach ($fields_array as $field => $type) {
            if ($type === "") {
                $type = 'VARCHAR(255)';
            }
            $sql .= $field . "  " . $type . ",";
        }
        foreach ($indices_array as $index => $field) {
            $sql .= "{$field} ({$index}),";
        }
        $sql = rtrim($sql, ',');
        $sql .= ")";

        if ($conn->query($sql) === TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function getColumnsArray($table_name) {

    // returns array with column name as header and type as value
    $file_path = $GLOBALS['res_file'];
    $resource = $table_name . '_headers';
    $headers = getResourceFromXML($file_path, $resource, 'type');

    return $headers;
}

function getTables() {

    $conn = openDB('rwk_productchooserdb');
    $result = $conn->query("SELECT table_name FROM information_schema.tables where table_schema='rwk_productchooserdb'");

    $html = array();
    foreach ($result as $table) {
        if (stripos($table['table_name'], '_groups') === FALSE) {
            $html[] = "<input id='{$table['table_name']}' class='button_class gen_table_btn'  type='button' name= '{$table['table_name']}' value='{$table['table_name']}' />";
        }
    }
    echo implode(' ', $html);
}

function insertRow($conn, $data, $table_name, $concat) {

    $exclude = array();

    foreach ($data as $key => $value) {
        if (!in_array($key, $exclude)) {

            $value = $conn->real_escape_string($value);
            $value = "'$value'";
            $assignments[] = "$key = $value";
        }
    }

    $insert_assignments = implode(",", $assignments);

    $insert = "INSERT INTO $table_name SET $insert_assignments ";
    $duplicate = "ON DUPLICATE KEY ";
    $update = "UPDATE $insert_assignments";
    if ($concat) {
        $if_id = "IF (INSTR(Variant_IDs, '{$data['Variant_IDs']}') > 0, Variant_IDs, CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}'))";
        $if_colour = "IF (INSTR(Colour, '{$data['Colour']}') > 0, Colour, CONCAT_WS(',', Colour, '{$data['Colour']}'))";
        $if_size = "IF (INSTR(Size, '{$data['Size']}') > 0, Size, CONCAT_WS(',', Size, '{$data['Size']}'))";
        $update = "UPDATE SKU = SKU, Name = Name, Variant_IDs = $if_id, Colour = $if_colour, Size = $if_size";
//        $if = "IF (INSTR(Variant_IDs, '{$data['Variant_IDs']}') > 0, Variant_IDs, CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}', Colour, '{$data['Colour']}', Size, '{$data['Size']}'))";
//        $update = "UPDATE SKU = SKU, Name = Name, Variant_IDs = $if";
//        $update = "UPDATE Base_SKU = Base_SKU, Variant_IDs = CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}')";
    }

    $sql = $insert . $duplicate . $update;
    if ($conn->query($sql)) {
        return TRUE;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function getRow($conn, $table_name, $row) {

    $result = $conn->query("SELECT * FROM {$table_name} LIMIT {$row}, 1");
//    $data = $result->fetch_row();
    $data = $result->fetch_assoc();

    if ($data != null) {
        return $data;
    } else {
        return FALSE;
    }
}

function getProductByID($table_name, $ID) {

        $conn = openDB('rwk_productchooserdb');
        $result = $conn->query("SELECT * FROM {$table_name} WHERE Product_ID IN ({$ID})");
        return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductData($conn, $table_name, $start_row, $items_per_page) {

    $table = $table_name . '_groups';
    $groups_results = $conn->query("SELECT * FROM {$table} LIMIT {$start_row}, {$items_per_page}");

    if ($groups_results !== FALSE) {

        $table = $table_name . '_variants';

        foreach ($groups_results as $group_row) {
            $variants[] = $group_row['Variant_IDs'];
            $variant_results = $conn->query("SELECT * FROM {$table} WHERE Product_ID IN ({$group_row['Variant_IDs']})");

            if ($variant_results !== FALSE) {

                foreach ($variant_results as $variant_row) {
                    $grouped_variants[$variant_row['Product_ID']] = [
                        'Selling' => $variant_row['Selling'],
                        'Product_ID' => $variant_row['Product_ID'],
                        'Name' => $variant_row['Name'],
                        'SKU' => $variant_row['SKU'],
                        'Price_RRP' => $variant_row['Price_RRP'],
                        'Trade_Price' => $variant_row['Trade_Price'],
                        'Description' => $variant_row['Description'],
                        'Image' => $variant_row['Image'],
                        'Colour' => $variant_row['Colour'],
                        'Size' => $variant_row['Size']
                    ];
                }
                $array[] = $grouped_variants;
                unset($grouped_variants);
            }
        }
        return $array;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function updateSellingDB($conn, $table_name, $selling_list) {

    $checkbox = json_decode($selling_list, TRUE);
    $update = "UPDATE {$table_name} SET Selling = {$checkbox['checked']} WHERE Product_ID = {$checkbox['id']}";

    $sql = $update;
    if ($conn->query($sql)) {
        return TRUE;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function get_largest_id($table_name) {

    $conn = openDB('rwk_productchooserdb');
    $result = $conn->query("SELECT MAX(Product_ID) AS max_id FROM $table_name");
    $row = $result->fetch_assoc();
    return $row['max_id'];
}
